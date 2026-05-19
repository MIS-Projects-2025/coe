<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purpose;
use App\Services\PurposeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurposeController extends Controller
{
    public function __construct(protected PurposeService $service) {}

    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $perPage = (int) $request->input('per_page', 10);

        $records = Purpose::query()
            ->when($search, fn($q) => $q->where('purpose', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Purpose/Index', [
            'records' => $records,
            'filters' => ['search' => $search, 'per_page' => $perPage],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose' => 'required|string|max:500|unique:purpose,purpose',
        ]);

        $empData = session('emp_data');

        $this->service->create([
            'purpose'             => $validated['purpose'],
            'created_by_emp_num'  => $empData['emp_id'] ?? null,
            'date_created'        => Carbon::now(),
        ]);

        return back()->with('success', 'Purpose added successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'purpose' => "required|string|max:500|unique:purpose,purpose,{$id}",
        ]);

        $empData = session('emp_data');

        $this->service->update($id, [
            'purpose'            => $validated['purpose'],
            'updated_by_emp_num' => $empData['emp_id'] ?? null,
            'date_updated'       => Carbon::now(),
        ]);

        return back()->with('success', 'Purpose updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return back()->with('success', 'Purpose deleted successfully.');
    }
}
