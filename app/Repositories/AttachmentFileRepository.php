<?php

namespace App\Repositories;

use App\Models\AttachmentFile;
use Illuminate\Database\Eloquent\Collection;

class AttachmentFileRepository
{
    public function __construct(protected AttachmentFile $model) {}

    public function findByEmployId(string $employId): Collection
    {
        return $this->model->where('employid', $employId)->get();
    }

    public function findByFileId(string $fileId): ?AttachmentFile
    {
        return $this->model->where('file_id', $fileId)->first();
    }

    public function create(array $data): AttachmentFile
    {
        return $this->model->create($data);
    }

    public function delete(AttachmentFile $record): bool
    {
        return $record->delete();
    }
}
