<?php

namespace App\Services;

use App\Models\AdminList;
use App\Repositories\AdminListRepository;
use Illuminate\Database\Eloquent\Collection;

class AdminListService
{
    public function __construct(protected AdminListRepository $repo) {}

    public function getAll(): Collection
    {
        return $this->repo->all();
    }

    public function getById(int $id): ?AdminList
    {
        return $this->repo->findById($id);
    }

    public function getByAdminId(string $adminId): ?AdminList
    {
        return $this->repo->findByAdminId($adminId);
    }

    public function create(array $data): AdminList
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): ?AdminList
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
