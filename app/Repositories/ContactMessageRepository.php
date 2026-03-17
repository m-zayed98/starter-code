<?php

namespace App\Repositories;

use App\Http\Filters\ContactMessageFilter;
use App\Models\ContactMessage;
use App\Repositories\Contracts\ContactMessageRepositoryContract;
use Illuminate\Database\Eloquent\Model;

class ContactMessageRepository extends BaseRepository implements ContactMessageRepositoryContract
{
    /**
     * Resolve the model instance.
     *
     * @return Model
     */
    protected function resolveModel(): Model
    {
        return new ContactMessage();
    }

    /**
     * Resolve the filter instance.
     *
     * @return ContactMessageFilter|null
     */
    protected function resolveFilter(): ?ContactMessageFilter
    {
        return new ContactMessageFilter(request());
    }
}