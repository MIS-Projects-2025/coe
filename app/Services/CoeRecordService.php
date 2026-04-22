<?php

namespace App\Services;

use App\Models\CoeRecord;
use App\Repositories\CoeRecordRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class CoeRecordService
{
    public function __construct(protected CoeRecordRepository $repo) {}

    /**
     * Return paginated COE records from hashed query parameters.
     * All filter/sort/page params come from the hash-decoded request.
     */
    public function getPaginated(array $params): LengthAwarePaginator
    {
        return $this->repo->paginate(
            perPage: (int) ($params['per_page'] ?? 10),
            search: $params['search']   ?? null,
            status: $params['status']   ?? null,
            coeType: $params['coe_type'] ?? null,
            sortBy: $params['sort_by']  ?? 'id',
            sortDir: $params['sort_dir'] ?? 'desc',
        );
    }

    public function getById(int $id): ?CoeRecord
    {
        return $this->repo->findById($id);
    }

    public function create(array $data): CoeRecord
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): ?CoeRecord
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
