<?php

namespace App\Repositories;

use App\Models\AdminList;
use Illuminate\Database\Eloquent\Collection;

class AdminListRepository
{
    public function __construct(protected AdminList $model) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?AdminList
    {
        return $this->model->find($id);
    }

    public function findByAdminId(string $adminId): ?AdminList
    {
        return $this->model->where('admin_id', $adminId)->first();
    }

    public function create(array $data): AdminList
    {
        return $this->model->create($data);
    }

    public function update(AdminList $record, array $data): AdminList
    {
        $record->update($data);
        return $record->fresh();
    }

    public function delete(AdminList $record): bool
    {
        return $record->delete();
    }
}
