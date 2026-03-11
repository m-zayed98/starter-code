<?php

namespace App\Services;

use App\Repositories\Contracts\AdminRepositoryContract;

class AdminService extends BaseModelService
{
    /**
     * Create a new service instance.
     *
     * @param AdminRepositoryContract $repository
     */
    public function __construct(AdminRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Prepare data for create operation.
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForCreate(array $data): array
    {
        // Handle file uploads using UploadService facade
        // Example: $data['image'] = UploadService::upload($data['image'], 'entity-folder');
        
        return $data;
    }

    /**
     * Prepare data for update operation.
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForUpdate(array $data): array
    {
        // Handle file uploads using UploadService facade
        // Example: if (isset($data['image'])) {
        //     $data['image'] = UploadService::upload($data['image'], 'entity-folder');
        // }
        
        return $data;
    }
}