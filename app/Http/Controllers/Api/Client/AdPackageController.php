<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\AdPackageResource;
use App\Models\AdPackage;
use App\Repositories\Contracts\AdPackageRepositoryContract;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdPackageController extends Controller
{
    public function __construct(
        private readonly AdPackageRepositoryContract $adPackageRepository,
        private readonly SubscriptionService         $subscriptionService,
    ) {}

    /**
     * GET /packages
     *
     * List all visible packages (offers first, then normal).
     *
     * For authenticated users:
     *  - Uses `getActivePackageId` (scalar query, no model hydration) to
     *    determine which package the user is subscribed to.
     *  - The subscribed package is stamped with `is_subscribed = true` and
     *    carries an `active_subscription` block with quota/progress-bar data.
     *  - Offer packages where the subscriber cap is reached remain visible
     *    to the subscribed user via `getVisiblePackagesForSubscriber`.
     *
     * N+1 prevention:
     *  - `activeSubscriptions` is eager-loaded by the repository for all
     *    packages in a single query (needed for offer subscriber counts).
     *  - The user's active subscription is fetched once (scalar query for
     *    index, full model only when the user is subscribed to a package
     *    in the current page).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user    = auth('api')->user();
        $perPage = (int) $request->query('per_page', 15);

        /** @var LengthAwarePaginator $packages */
        $packages = $user
            ? $this->adPackageRepository->getVisiblePackagesForSubscriber($user->id, $perPage)
            : $this->adPackageRepository->getVisiblePackages($perPage);

        // Scalar query — just the package ID, no model hydration
        $activePackageId = $user
            ? $this->subscriptionService->getActivePackageId($user->id)
            : null;

        // Only load the full subscription model if the subscribed package
        // actually appears on this page (avoids the query entirely for guests
        // and for pages that don't contain the subscribed package).
        $activeSubscription = null;

        if ($activePackageId !== null) {
            $subscribedOnPage = $packages->getCollection()
                ->contains(fn (AdPackage $p) => $p->id === $activePackageId);

            if ($subscribedOnPage) {
                $activeSubscription = $this->subscriptionService->getActiveSubscription($user->id);
            }
        }

        // Stamp each package with subscription context before resource transformation
        $packages->getCollection()->transform(
            function (AdPackage $package) use ($activePackageId, $activeSubscription) {
                $isSubscribed = ($activePackageId !== null && $package->id === $activePackageId);

                $package->is_subscribed       = $isSubscribed;
                $package->active_subscription = $isSubscribed ? $activeSubscription : null;

                return $package;
            }
        );

        return ApiResponse::respondWithCollection(
            AdPackageResource::collection($packages),
        )->withPagination()->send();
    }

    /**
     * GET /packages/{id}
     *
     * Show a single package detail with subscription context.
     *
     * `activeSubscriptions` is eager-loaded by the repository (single query).
     * The user's active subscription is fetched only when needed.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var AdPackage $package */
        $package = $this->adPackageRepository->showOrFail($id, [
            'relations' => ['activeSubscriptions'],
        ]);

        /** @var \App\Models\User|null $user */
        $user               = auth('api')->user();
        $activeSubscription = null;
        $isSubscribed       = false;

        if ($user !== null) {
            $activePackageId = $this->subscriptionService->getActivePackageId($user->id);
            $isSubscribed    = $activePackageId === $package->id;

            if ($isSubscribed) {
                $activeSubscription = $this->subscriptionService->getActiveSubscription($user->id);
            }
        }

        $package->is_subscribed       = $isSubscribed;
        $package->active_subscription = $activeSubscription;

        return ApiResponse::respondWithModel(
            new AdPackageResource($package),
        )->send();
    }
}
