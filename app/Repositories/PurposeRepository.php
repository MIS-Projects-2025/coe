<?php

namespace App\Repositories;

use App\Models\Purpose;
use Illuminate\Database\Eloquent\Collection;

class PurposeRepository
{
    public function __construct(protected Purpose $model) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Purpose
    {
        return $this->model->find($id);
    }

    public function create(array $data): Purpose
    {
        return $this->model->create($data);
    }

    public function update(Purpose $record, array $data): Purpose
    {
        $record->update($data);
        return $record->fresh();
    }

    public function delete(Purpose $record): bool
    {
        return $record->delete();
    }
}
