<?php

namespace App\Services;

use App\Enums\ContactMessageStatus;
use App\Mail\ContactMessageReplyMail;
use App\Models\ContactMessage;
use App\Repositories\Contracts\ContactMessageRepositoryContract;
use Illuminate\Support\Facades\Mail;

class ContactMessageService extends BaseModelService
{
    /**
     * Create a new service instance.
     *
     * @param ContactMessageRepositoryContract $repository
     */
    public function __construct(ContactMessageRepositoryContract $repository)
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

    public function reply(int $id, string $reply): ContactMessage
    {
        /** @var ContactMessage $contactMessage */
        $contactMessage = $this->repository->showOrFail($id);

        $contactMessage = $this->repository->update($contactMessage->id, [
            'reply' => $reply,
            'status' => ContactMessageStatus::REPLIED,
            'replied_at' => now(),
        ]);

        if (!empty($contactMessage->email)) {
            try {
                Mail::to($contactMessage->email)->send(new ContactMessageReplyMail($contactMessage));
            } catch (\Exception $e) {
                // TODO: Handle email sending error
            }
        }

        return $contactMessage;
    }
}
