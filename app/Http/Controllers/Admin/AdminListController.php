<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminList;
use App\Services\AdminListService;
use App\Services\HrisApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminListController extends Controller
{
    public function __construct(
        protected AdminListService $service,
        protected HrisApiService   $hris,
    ) {}

    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $perPage = (int) $request->input('per_page', 10);

        $paginated = AdminList::query()
            ->when($search, fn($q) => $q->where('admin_id', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Enrich each row with HRIS employee data
        $adminIds = $paginated->pluck('admin_id')->filter()->values()->toArray();

        $employees = $adminIds
            ? DB::connection('masterlist')
                ->table('employee_masterlist')
                ->whereIn('EMPLOYID', $adminIds)
                ->get(['EMPLOYID', 'EMPNAME', 'JOB_TITLE', 'DEPARTMENT'])
                ->keyBy('EMPLOYID')
            : collect();

        $paginated->through(function ($row) use ($employees) {
            $emp = $employees[$row->admin_id] ?? null;
            $row->emp_name     = $emp?->EMPNAME    ?? '—';
            $row->emp_position = $emp?->JOB_TITLE  ?? '—';
            $row->emp_dept     = $emp?->DEPARTMENT ?? '—';
            return $row;
        });

        return Inertia::render('Admin/AdminList/Index', [
            'records' => $paginated,
            'filters' => ['search' => $search, 'per_page' => $perPage],
        ]);
    }

    /**
     * JSON endpoint for the employee combobox — proxies fetchActiveEmployees from HRIS.
     */
    public function searchEmployees(Request $request)
    {
        $search  = (string) ($request->input('search') ?? '');
        $page    = (int) ($request->input('page') ?? 1);

        $result  = $this->hris->fetchActiveEmployees($search, $page, 20);

        $options = array_map(fn($emp) => [
            'value' => (string) ($emp['employid'] ?? ''),
            'label' => ($emp['employid'] ?? '') . ' - ' . ($emp['emp_name'] ?? ''),
        ], $result['data']);

        return response()->json([
            'options' => $options,
            'hasMore' => $result['hasMore'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|string|max:45|unique:admin_list,admin_id',
        ]);

        $empName = $this->hris->fetchEmployeeName((int) $validated['admin_id']);

        if (! $empName) {
            return back()->withErrors(['admin_id' => 'Employee not found in HRIS.']);
        }

        $empData = session('emp_data');

        $this->service->create([
            'admin_id'           => $validated['admin_id'],
            'created_by_emp_num' => $empData['emp_id'] ?? null,
            'date_created'       => Carbon::now(),
        ]);

        return back()->with('success', "{$empName} has been added as an admin.");
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return back()->with('success', 'Admin removed successfully.');
    }
}
