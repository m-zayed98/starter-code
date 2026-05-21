<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueTranslation implements ValidationRule
{
    public function __construct(
        protected string $table,
        protected string $column,
        protected string $locale,
        protected ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT({$this->column}, '$.{$this->locale}')) = ?",
                [$value]
            );
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            // Laravel treats dots as array notation in __(), replace with underscore
            $key = str_replace('.', '_', $attribute);
            $translatedAttribute = __('validation.attributes.'.$key);

            // Fall back to raw attribute name if no translation found
            if ($translatedAttribute === 'validation.attributes.'.$key) {
                $translatedAttribute = $attribute;
            }

            $fail(__('validation.unique_translation', ['attribute' => $translatedAttribute]));
        }
    }
}
