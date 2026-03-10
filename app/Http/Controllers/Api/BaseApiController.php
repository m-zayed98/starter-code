<?php

namespace App\Http\Controllers\Api;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repositories\DTOs\QueryOptions;
use App\Services\BaseModelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  BaseApiController                                                      │
 * │                                                                         │
 * │  Wires together BaseModelService + ApiResponse for standard CRUD.       │
 * │                                                                         │
 * │  Child class must define:                                               │
 * │    protected string $modelName   – singular PascalCase, e.g. 'User'    │
 * │    protected string $serviceName – FQCN of the service                  │
 * │    protected string $resource    – FQCN of the JsonResource             │
 * │                                                                         │
 * │  Child class may override:                                              │
 * │    protected string $storeRequest  – FQCN of store FormRequest          │
 * │    protected string $updateRequest – FQCN of update FormRequest         │
 * │    protected bool   $usePermissions – registers permission middleware   │
 * │    protected array  $queryOptions   – per-action QueryOptions config    │
 * │    protected function beforeStore(array $data): array                   │
 * │    protected function beforeUpdate(array $data): array                  │
 * │    protected function statusColumn(): string  – default 'is_active'     │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
abstract class BaseApiController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    //  Required in child class
    // ══════════════════════════════════════════════════════════════════════

    /** Singular PascalCase model name, e.g. 'User', 'BlogPost'. */
    protected string $modelName = '';

    /** FQCN of the service class (must extend BaseModelService). */
    protected string $serviceName = '';

    /** FQCN of the JsonResource used for single-model responses. */
    protected string $resource = '';

    /**
     * FQCN of the FormRequest to resolve and validate for store().
     * Leave empty to use the raw incoming request (no extra validation class).
     */
    protected string $storeRequest = '';

    /**
     * FQCN of the FormRequest to resolve and validate for update().
     * Leave empty to use the raw incoming request (no extra validation class).
     */
    protected string $updateRequest = '';

    /**
     * Set to true to auto-register permission middleware for all CRUD actions.
     * Default permission names are derived from $modelName (snake_case singular).
     */
    protected bool $usePermissions = false;

    /**
     * Per-action QueryOptions overrides.
     * Keys: index | show | store | update | destroy | toggleStatus
     *
     * Example:
     *   protected array $queryOptions = [
     *       'index' => ['relations' => ['profile'], 'perPage' => 20, 'applyFilters' => true],
     *       'show'  => ['relations' => ['profile', 'roles']],
     *   ];
     */
    protected array $queryOptions = [];

    // ══════════════════════════════════════════════════════════════════════
    //  Resolved service instance
    // ══════════════════════════════════════════════════════════════════════

    protected BaseModelService $service;

    public function __construct()
    {
        $this->service = app($this->serviceName);
    }

    public static function middleware(): array
    {
        $instance = new static();

        if (! $instance->usePermissions) {
            return [];
        }

        $name = $instance->modelName();

        return [
            new Middleware("permission:index-{$name}",         only: ['index']),
            new Middleware("permission:show-{$name}",          only: ['show']),
            new Middleware("permission:create-{$name}",        only: ['store']),
            new Middleware("permission:update-{$name}",        only: ['update']),
            new Middleware("permission:destroy-{$name}",       only: ['destroy']),
            new Middleware("permission:toggle-status-{$name}", only: ['toggleStatus']),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  CRUD actions
    // ══════════════════════════════════════════════════════════════════════

    public function index(Request $request): JsonResponse
    {
        $options    = $this->resolveQueryOptions('index');
        $result     = $this->service->get($options);
        $collection = $this->resource::collection($result);

        $response = ApiResponse::respondWithCollection($collection);

        if ($options->isPaginated()) {
            $response->withPagination();
        }

        return $response->send();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $model = $this->service->showOrFail($id, $this->resolveQueryOptions('show'));

        return ApiResponse::respondWithModel(new $this->resource($model))->send();
    }

    public function store(Request $request): JsonResponse
    {
        $data  = $this->resolveFormRequest($this->storeRequest)->validated();
        $model = $this->service->create($data);

        return ApiResponse::respondWithModel(
            new $this->resource($model),
            message   : $this->modelName() . ' created successfully.',
            httpStatus: 201,
        )->send();
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data  = $this->resolveFormRequest($this->updateRequest)->validated();
        $model = $this->service->update($id, $data);

        return ApiResponse::respondWithModel(
            new $this->resource($model),
            message: $this->modelName() . ' updated successfully.',
        )->send();
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($id);

        return ApiResponse::respondWithArray(
            [],
            message: $this->modelName() . ' deleted successfully.',
        )->send();
    }

    /**
     * Toggle the model's boolean status column (default: is_active).
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $column = $this->statusColumn();
        $model  = $this->service->showOrFail($id);

        $this->service->update($id, [$column => ! $model->{$column}]);

        $updated = $this->service->showOrFail($id, $this->resolveQueryOptions('show'));

        return ApiResponse::respondWithModel(
            new $this->resource($updated),
            message: $this->modelName() . ' status updated successfully.',
        )->send();
    }
    
    /**
     * The boolean column toggled by toggleStatus().
     * Override if your model uses a different column name.
     */
    protected function statusColumn(): string
    {
        return 'is_active';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Internals
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Derives snake_case singular name from $modelName.
     * e.g.  'User' → 'user'   |   'BlogPost' → 'blog_post'
     */
    protected function modelName(): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $this->modelName));
    }

    private function resolveQueryOptions(string $action): QueryOptions
    {
        return QueryOptions::make($this->queryOptions[$action] ?? []);
    }

    /**
     * Resolve and validate a FormRequest class, or fall back to the
     * current request if no class is specified.
     */
    private function resolveFormRequest(string $requestClass): Request
    {
        if ($requestClass === '') {
            return request();
        }

        return app($requestClass);
    }
}
