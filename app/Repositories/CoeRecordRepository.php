<?php

namespace App\Repositories;

use App\Models\CoeRecord;
use Illuminate\Pagination\LengthAwarePaginator;

class CoeRecordRepository
{
    public function __construct(protected CoeRecord $model) {}



    public function paginate(
        int $perPage = 10,
        int $page = 1,
        ?string $search = null,
        ?string $status = null,
        ?string $coeType = null,
        string $sortBy = 'id',
        string $sortDir = 'desc'
    ): LengthAwarePaginator {
        $allowedSorts = ['id', 'employid', 'purpose', 'date_request', 'coe_type', 'status', 'pcn_status'];

        $sortBy  = in_array($sortBy, $allowedSorts) ? $sortBy : 'id';
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'desc';

        return $this->model->newQuery()
            ->when(
                $search,
                fn($q) => $q->where('employid', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
            )
            ->when($status,  fn($q) => $q->where('status',   $status))
            ->when($coeType, fn($q) => $q->where('coe_type', $coeType))
            ->orderBy($sortBy, $sortDir)
            ->paginate(perPage: $perPage, page: $page);  // ← pass page, drop withQueryString()
    }

    public function findById(int $id): ?CoeRecord
    {
        return $this->model->find($id);
    }

    public function create(array $data): CoeRecord
    {
        return $this->model->create($data);
    }

    public function update(CoeRecord $record, array $data): CoeRecord
    {
        $record->update($data);
        return $record->fresh();
    }

    public function delete(CoeRecord $record): bool
    {
        return $record->delete();
    }
}
