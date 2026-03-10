<?php

namespace App\Http\Controllers\Api\Admin;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /**
     * Return all permissions (no pagination — used for dropdowns / role forms).
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('name')->get();

        return ApiResponse::respondWithCollection(
            PermissionResource::collection($permissions),
        )->send();
    }
}
