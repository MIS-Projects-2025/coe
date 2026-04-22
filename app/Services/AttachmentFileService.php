<?php

namespace App\Services;

use App\Models\AttachmentFile;
use App\Repositories\AttachmentFileRepository;
use Illuminate\Database\Eloquent\Collection;

class AttachmentFileService
{
    public function __construct(protected AttachmentFileRepository $repo) {}

    public function getByEmployee(string $employId): Collection
    {
        return $this->repo->findByEmployId($employId);
    }

    public function getByFileId(string $fileId): ?AttachmentFile
    {
        return $this->repo->findByFileId($fileId);
    }

    public function create(array $data): AttachmentFile
    {
        return $this->repo->create($data);
    }

    public function delete(string $fileId): bool
    {
        $record = $this->repo->findByFileId($fileId);

        if (! $record) {
            return false;
        }

        return $this->repo->delete($record);
    }
}
