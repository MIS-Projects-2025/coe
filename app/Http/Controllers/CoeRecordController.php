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
     * Render the COE records table page.
     * Query params are hash-encoded from the frontend and decoded here.
     * Records are deferred (lazy) so the page shell loads immediately.
     */
    public function index(Request $request): Response
    {
        // Decode hash param into query array
        // Frontend encodes filters as base64 JSON in ?q= hash param
        $params = $this->decodeHashParams($request->query('q'));

        return Inertia::render('CoeRecord/Index', [
            // Eagerly pass filter state so the frontend can restore UI
            'filters' => [
                'search'   => $params['search']   ?? '',
                'status'   => $params['status']   ?? '',
                'coe_type' => $params['coe_type'] ?? '',
                'sort_by'  => $params['sort_by']  ?? 'id',
                'sort_dir' => $params['sort_dir'] ?? 'desc',
                'per_page' => (int) ($params['per_page'] ?? 10),
                'page'     => (int) ($params['page']     ?? 1),
            ],

            // Deferred (lazy) — only resolved when the frontend requests it
            'records' => Inertia::defer(
                fn() =>
                $this->service->getPaginated($params)
            ),
        ]);
    }

    /**
     * Decode base64 JSON hash param into an array.
     * Falls back to an empty array if invalid or missing.
     */
    private function decodeHashParams(?string $hash): array
    {
        if (! $hash) {
            return [];
        }

        try {
            $decoded = base64_decode($hash, strict: true);

            if ($decoded === false) {
                return [];
            }

            $params = json_decode($decoded, associative: true);

            return is_array($params) ? $params : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
