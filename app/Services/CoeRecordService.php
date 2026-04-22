<?php

namespace App\Services;

use App\Repositories\CoeRecordRepository;
use App\Repositories\AttachmentFileRepository;
use App\Repositories\PurposeRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
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



    public function getPaginatedRecords(array $params): LengthAwarePaginator
    {
        return $this->coeRecordRepo->paginate(
            perPage: (int) ($params['per_page'] ?? 10),
            page: (int) ($params['page']     ?? 1),
            search: $params['search']   ?? null,
            status: $params['status']   ?? null,
            coeType: $params['coe_type'] ?? null,
            sortBy: $params['sort_by']  ?? 'id',
            sortDir: $params['sort_dir'] ?? 'desc',
        );
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
     * Create new COE request with attachment.
     */
    public function createRequest(array $validatedData, ?UploadedFile $attachment, array $empData): array
    {
        try {
            // Prepare COE record data
            $coeData = [
                'employid' => $empData['emp_id'],
                'purpose' => $validatedData['purpose'],
                'coe_type' => $validatedData['coe_type'],
                'date_request' => now(),
                'status' => 'pending',
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

            $updateData = ['status' => $status];
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
