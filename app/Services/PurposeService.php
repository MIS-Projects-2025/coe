<?php

namespace App\Services;

use App\Models\Purpose;
use App\Repositories\PurposeRepository;
use Illuminate\Database\Eloquent\Collection;

class PurposeService
{
    public function __construct(protected PurposeRepository $repo) {}

    public function getAll(): Collection
    {
        return $this->repo->all();
    }

    public function getById(int $id): ?Purpose
    {
        return $this->repo->findById($id);
    }

    public function create(array $data): Purpose
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): ?Purpose
    {
        $record = $this->repo->findById($id);

        if (! $record) {
            return null;
        }

        return $this->repo->update($record, $data);
    }

    public function delete(int $id): bool
    {
        $record = $this->repo->findById($id);

        if (! $record) {
            return false;
        }

        return $this->repo->delete($record);
    }
}
