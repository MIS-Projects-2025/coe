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
        string $sortDir = 'desc',
        ?string $tab = null,
        ?array $scopeIds = null
    ): LengthAwarePaginator {
        $allowedSorts = ['id', 'employid', 'purpose', 'date_request', 'coe_type', 'status', 'pcn_status'];

        $sortBy  = in_array($sortBy, $allowedSorts) ? $sortBy : 'id';
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'desc';

        // Pending tab: status 0 (For Approval) and 1 (Approved/For Processing).
        // History tab: status 2 (Generated), 3 (Disapproved), 5 (Available for Claim).
        $pendingStatuses = [0, 1];

        return $this->model->newQuery()
            ->when(
                $search,
                fn($q) => $q->where('employid', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
            )
            ->when($tab === 'pending', fn($q) => $q->whereIn('status', $pendingStatuses))
            ->when($tab === 'history', fn($q) => $q->whereNotIn('status', $pendingStatuses))
            ->when(!$tab && $status !== null && $status !== '', fn($q) => $q->where('status', $status))
            ->when($coeType, fn($q) => $q->where('coe_type', $coeType))
            ->when($scopeIds !== null, fn($q) => $q->whereIn('employid', $scopeIds))
            ->orderBy($sortBy, $sortDir)
            ->paginate(perPage: $perPage, page: $page);
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
