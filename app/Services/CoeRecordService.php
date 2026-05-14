<?php

namespace App\Services;

use App\Repositories\CoeRecordRepository;
use App\Repositories\AttachmentFileRepository;
use App\Repositories\PurposeRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CoeRecordService
{
    public function __construct(
        protected CoeRecordRepository $coeRecordRepo,
        protected AttachmentFileRepository $attachmentRepo,
        protected PurposeRepository $purposeRepo,
        protected HrisApiService $hrisService
    ) {}



    public function getPaginatedRecords(array $params, ?array $scopeIds = null): LengthAwarePaginator
    {
        return $this->coeRecordRepo->paginate(
            perPage: (int) ($params['per_page'] ?? 10),
            page: (int) ($params['page']     ?? 1),
            search: $params['search']   ?? null,
            status: $params['status']   ?? null,
            coeType: $params['coe_type'] ?? null,
            sortBy: $params['sort_by']  ?? 'id',
            sortDir: $params['sort_dir'] ?? 'desc',
            tab: $params['tab']         ?? 'pending',
            scopeIds: $scopeIds,
        );
    }

    /**
     * Determine which employee IDs the current user may see, and whether they
     * are an HR admin (who can approve/disapprove records).
     *
     * Returns:
     *   scope_ids  – null = no restriction (HR admin sees all)
     *              – array of employids (own + direct reports)
     *   is_hr_admin – true when user is in admin_list AND HR department
     */
    public function getEmpScope(string $empId, bool $isAdminListMember): array
    {
        // Fetch this employee's department from the masterlist
        $emp = DB::connection('masterlist')
            ->table('employee_masterlist')
            ->where('EMPLOYID', $empId)
            ->first(['DEPARTMENT']);

        $dept       = $emp->DEPARTMENT ?? '';
        $isHrAdmin  = $isAdminListMember && strtolower($dept) === 'human resource';

        if ($isHrAdmin) {
            return ['scope_ids' => null, 'is_hr_admin' => true];
        }

        // Find all employees who list this person as APPROVER1 or APPROVER2
        $subordinateIds = DB::connection('masterlist')
            ->table('employee_masterlist')
            ->where('APPROVER1', $empId)
            ->orWhere('APPROVER2', $empId)
            ->pluck('EMPLOYID')
            ->map(fn($id) => (string) $id)
            ->toArray();

        // Always include the employee's own records
        $scopeIds = array_values(array_unique(array_merge([$empId], $subordinateIds)));

        return ['scope_ids' => $scopeIds, 'is_hr_admin' => false];
    }
    /**
     * Get all purposes for dropdown.
     */
    public function getAllPurposes(): \Illuminate\Support\Collection
    {
        return $this->purposeRepo->all();
    }

    /**
     * Get employee data for form.
     */
    public function getEmployeeFormData(array $empData): array
    {
        // Fetch employee details from HRIS API
        $employeeDetails = $this->hrisService->fetchWorkDetails($empData['emp_id'] ?? null);


        return [
            'employee' => [
                'employid' => $empData['emp_id'] ?? null,
                'emp_name' => $empData['emp_name'] ?? null,
                'emp_firstname' => $empData['emp_firstname'] ?? null,
                'position' => $employeeDetails['emp_jobtitle'] ?? null,
                'department' => $employeeDetails['emp_dept'] ?? null,
                'prodline' => $employeeDetails['emp_prodline'] ?? null,
                'date_hired' => $employeeDetails['date_hired'] ?? null,
                'emp_status' => $employeeDetails['emp_status'] ?? null,
            ],

        ];
    }

    /**
     * Gather all data needed to preview and print a COE for a given record.
     *
     * Returns an array with:
     *   emp_name, emp_sex, date_hired, prodline, emp_position
     *   sep_date       – populated for Inactive (coe_type 2)
     *   salary_data    – populated for With Compensation (coe_type 3)
     *
     * Returns null when the record does not exist.
     * Returns ['error' => '...'] when employee data cannot be found.
     */
    public function getGenerateData(int $id): ?array
    {
        $record = $this->coeRecordRepo->findById($id);
        if (!$record) {
            return null;
        }

        // ── Fetch employee details from the HRIS masterlist DB ────────────────
        $emp = DB::connection('masterlist')
            ->table('employee_masterlist')
            ->where('EMPLOYID', $record->employid)
            ->first();

        if (!$emp) {
            return ['error' => "Employee {$record->employid} not found in HRIS masterlist."];
        }

        $coeType = (int) $record->coe_type;

        $result = [
            'emp_name'     => $emp->EMPNAME     ?? null,
            'emp_sex'      => $emp->EMPSEX      ?? null,
            'date_hired'   => $emp->DATEHIRED   ?? null,
            'prodline'     => $emp->PRODLINE     ?? null,
            // Fall back to masterlist position if it was not saved on the record
            'emp_position' => $record->emp_position ?? ($emp->JOB_TITLE ?? null),
        ];

        // ── Inactive (type 2): include separation date ────────────────────────
        if ($coeType === 2) {
            // Column name may vary — adjust DATERESIGNED to match your masterlist schema
            $result['sep_date'] = $emp->DATERESIGNED ?? $emp->DATE_RESIGNED ?? null;
        }

        // ── With Compensation (type 3): fetch salary breakdown ────────────────
        if ($coeType === 3) {
            $empClass = (int) ($record->emp_class ?? $emp->EMPCLASS ?? 1);
            $result['salary_data'] = $this->hrisService->fetchSalaryData(
                (int) $record->employid,
                $empClass
            );
        }

        return $result;
    }

    /**
     * Return attachments for a given COE record (looked up by employid).
     */
    public function getRecordAttachments(int $id): ?array
    {
        $record = $this->coeRecordRepo->findById($id);
        if (!$record) {
            return null;
        }

        $attachments = $this->attachmentRepo->findByEmployId($record->employid);

        return $attachments->map(function ($att) {
            $filePath = $att->file_location ?? null;
            return [
                'id'        => $att->id,
                'file_name' => $att->file_name ?? $att->original_file_name ?? 'attachment',
                'file_type' => $att->file_type,
                'file_size' => $att->file_size,
                'url'       => $filePath ? Storage::url($filePath) : null,
                'date_filed'=> optional($att->date_filed)->toDateTimeString(),
            ];
        })->values()->all();
    }

    /**
     * Bulk update status for multiple COE records.
     */
    public function bulkUpdateStatus(array $ids, string $status, ?string $remarks = null): array
    {
        try {
            $updateData = ['status' => $this->resolveStatusValue($status)];
            if ($remarks) {
                $updateData['remarks'] = $remarks;
            }

            foreach ($ids as $id) {
                $record = $this->coeRecordRepo->findById((int) $id);
                if ($record) {
                    $this->coeRecordRepo->update($record, $updateData);
                }
            }

            Log::info('COE bulk status updated', ['ids' => $ids, 'status' => $status]);

            return ['success' => true, 'message' => count($ids) . ' record(s) updated successfully'];
        } catch (\Exception $e) {
            Log::error('Failed to bulk update COE status', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to update records'];
        }
    }

    /**
     * Create new COE request with attachment.
     */
    public function createRequest(array $validatedData, ?UploadedFile $attachment, array $empData): array
    {
        try {
            // Prepare COE record data
            $workDetails = $this->hrisService->fetchWorkDetails((int) $empData['emp_id']);

            $coeData = [
                'employid'     => $empData['emp_id'],
                'purpose'      => $validatedData['purpose'],
                'coe_type'     => $validatedData['coe_type'],
                'date_request' => now(),
                'status'       => 0,
                'emp_position' => $workDetails['emp_jobtitle'] ?? null,
                'emp_class'    => $workDetails['emp_class_name'] ?? null,
            ];
            // Create COE record
            $coeRecord = $this->coeRecordRepo->create($coeData);

            // Handle attachment if provided
            if ($attachment) {
                $this->handleAttachment($attachment, $empData['emp_id'], $coeRecord->id);
            }

            Log::info('COE request created successfully', [
                'record_id' => $coeRecord->id,
                'employid' => $empData['emp_id'],
                'coe_type' => $validatedData['coe_type'],
            ]);

            return [
                'success' => true,
                'message' => 'COE request submitted successfully',
                'record_id' => $coeRecord->id,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create COE request', [
                'error' => $e->getMessage(),
                'emp_data' => $empData,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to submit COE request: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle file attachment storage.
     */
    protected function handleAttachment(UploadedFile $attachment, string $employid, int $recordId): void
    {
        // Generate unique file ID
        $fileId = Str::uuid()->toString();

        // Store file
        $extension = $attachment->getClientOriginalExtension();
        $fileName = "{$fileId}.{$extension}";
        $path = $attachment->storeAs("coe_attachments/{$employid}", $fileName, 'public');

        // Save attachment record
        $this->attachmentRepo->create([
            'file_id' => $fileId,
            'employid' => $employid,
            'record_id' => $recordId,
            'file_name' => $attachment->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $attachment->getSize(),
            'file_type' => $attachment->getMimeType(),
            'date_uploaded' => now(),
        ]);
    }

    /**
     * Map string status labels to the numeric values stored in the database.
     */
    private function resolveStatusValue(string $status): int
    {
        return match($status) {
            'pending'   => 0,
            'approved'  => 1,
            'generated' => 2,
            'rejected'  => 3,
            default     => (int) $status,
        };
    }

    /**
     * Update COE request status.
     */
    public function updateStatus(int $id, string $status, ?string $remarks = null): array
    {
        try {
            $record = $this->coeRecordRepo->findById($id);

            if (!$record) {
                return [
                    'success' => false,
                    'message' => 'COE record not found',
                ];
            }

            $updateData = ['status' => $this->resolveStatusValue($status)];
            if ($remarks) {
                $updateData['remarks'] = $remarks;
            }

            $this->coeRecordRepo->update($record, $updateData);

            Log::info('COE status updated', [
                'record_id' => $id,
                'status' => $status,
            ]);

            return [
                'success' => true,
                'message' => 'Status updated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update COE status', [
                'record_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update status',
            ];
        }
    }

    /**
     * Delete COE request and its attachment.
     */
    public function deleteRequest(int $id): array
    {
        try {
            $record = $this->coeRecordRepo->findById($id);

            if (!$record) {
                return [
                    'success' => false,
                    'message' => 'COE record not found',
                ];
            }

            // Delete attachment files
            $attachments = $this->attachmentRepo->findByEmployId($record->employid);
            foreach ($attachments as $attachment) {
                if ($attachment->file_path) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $this->attachmentRepo->delete($attachment);
            }

            // Delete COE record
            $this->coeRecordRepo->delete($record);

            Log::info('COE request deleted', [
                'record_id' => $id,
                'employid' => $record->employid,
            ]);

            return [
                'success' => true,
                'message' => 'COE request deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete COE request', [
                'record_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete COE request',
            ];
        }
    }
}
