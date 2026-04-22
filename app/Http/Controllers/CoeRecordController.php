<?php

namespace App\Http\Controllers;

use App\Services\CoeRecordService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoeRecordController extends Controller
{
    public function __construct(protected CoeRecordService $service) {}

    /**
     * Render COE records table page.
     */
    public function index(Request $request): Response
    {
        $params = $this->decodeHashParams($request->query('q'));

        return Inertia::render('CoeRecord/Index', [
            'filters' => [
                'search'   => $params['search']   ?? '',
                'status'   => $params['status']   ?? '',
                'coe_type' => $params['coe_type'] ?? '',
                'sort_by'  => $params['sort_by']  ?? 'id',
                'sort_dir' => $params['sort_dir'] ?? 'desc',
                'per_page' => (int) ($params['per_page'] ?? 10),
                'page'     => (int) ($params['page']     ?? 1),
            ],
            'records' => fn() => $this->service->getPaginatedRecords($params),
        ]);
    }

    /**
     * Render COE request form page.
     */
    public function create(): Response
    {
        $empData = session('emp_data');
        $employeeData = $this->service->getEmployeeFormData($empData);
        $purposes = $this->service->getAllPurposes();

        return Inertia::render('CoeRecord/Create', array_merge($employeeData, [
            'purposes' => $purposes,
        ]));
    }

    /**
     * Store new COE request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose' => 'required|string|max:500',
            'coe_type' => 'required|string|in:regular,special',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $empData = session('emp_data');

        $result = $this->service->createRequest(
            $validated,
            $request->file('attachment'),
            $empData
        );

        if ($result['success']) {
            return redirect()->route('coe.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Update COE request status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,generated',
            'remarks' => 'nullable|string|max:500',
        ]);

        $result = $this->service->updateStatus($id, $validated['status'], $validated['remarks'] ?? null);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Delete COE request.
     */
    public function destroy(int $id)
    {
        $result = $this->service->deleteRequest($id);

        if ($result['success']) {
            return redirect()->route('coe.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Decode base64 JSON hash param.
     */
    private function decodeHashParams(?string $hash): array
    {
        if (!$hash) return [];

        try {
            $decoded = base64_decode($hash, strict: true);
            if ($decoded === false) return [];
            $params = json_decode($decoded, associative: true);
            return is_array($params) ? $params : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
