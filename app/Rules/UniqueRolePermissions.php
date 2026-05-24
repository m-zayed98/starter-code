<?php

namespace App\Rules;

use App\Repositories\Contracts\RoleRepositoryContract;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueRolePermissions implements ValidationRule
{
    public function __construct(
        protected ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || empty($value)) {
            return;
        }

        /** @var RoleRepositoryContract $repository */
        $repository = app(RoleRepositoryContract::class);

        $existing = $repository->findRoleWithSamePermissions(
            array_map('intval', $value),
            $this->ignoreId,
        );

        if ($existing !== null) {
            $fail(__('validation.unique_role_permissions', [
                'role' => $existing->getTranslation('name', app()->getLocale()),
            ]));
        }
    }
}
