<?php

namespace App\Http\Controllers;

use App\Models\GateMachineStudyRequest;
use App\Models\StudyRequest;
use Illuminate\Http\JsonResponse;

class AdminNotificationController extends Controller
{
    /** GET /api/admin/notifications/summary */
    public function summary(): JsonResponse
    {
        $projectNew = StudyRequest::where('status', 'new')->count();
        $gateNew = GateMachineStudyRequest::where('status', 'new')->count();

        $latestProject = StudyRequest::where('status', 'new')
            ->latest()
            ->limit(5)
            ->get(['id', 'customer_name', 'customer_phone', 'project_location', 'created_at'])
            ->map(fn (StudyRequest $row) => [
                'kind' => 'project',
                'id' => $row->id,
                'customer_name' => $row->customer_name,
                'customer_phone' => $row->customer_phone,
                'location' => $row->project_location,
                'created_at' => $row->created_at?->toIso8601String(),
            ]);

        $latestGate = GateMachineStudyRequest::where('status', 'new')
            ->latest()
            ->limit(5)
            ->get(['id', 'customer_name', 'customer_phone', 'site_location', 'created_at'])
            ->map(fn (GateMachineStudyRequest $row) => [
                'kind' => 'gate',
                'id' => $row->id,
                'customer_name' => $row->customer_name,
                'customer_phone' => $row->customer_phone,
                'location' => $row->site_location,
                'created_at' => $row->created_at?->toIso8601String(),
            ]);

        $latest = $latestProject
            ->concat($latestGate)
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        return response()->json([
            'new_study_requests' => $projectNew,
            'new_gate_studies' => $gateNew,
            'new_total' => $projectNew + $gateNew,
            'latest' => $latest,
        ]);
    }
}
