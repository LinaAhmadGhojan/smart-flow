<?php

namespace App\Http\Controllers;

use App\Models\GateMachineStudyRequest;
use Illuminate\Http\Request;

class GateMachineStudyController extends Controller
{
    /** POST /api/gate-machine-studies — public submission */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'site_location' => 'required|string|max:500',
            'door_weight' => 'nullable|string|max:100',
            'door_width' => 'nullable|string|max:100',
            'door_height' => 'nullable|string|max:100',
            'door_material' => 'nullable|string|max:255',
            'has_electrical_point' => 'nullable|in:yes,no,unknown',
            'has_machine_wiring' => 'nullable|in:yes,no,unknown',
            'notes' => 'nullable|string|max:2000',
        ]);

        $study = GateMachineStudyRequest::create([
            ...$validated,
            'has_electrical_point' => $validated['has_electrical_point'] ?? 'unknown',
            'has_machine_wiring' => $validated['has_machine_wiring'] ?? 'unknown',
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'شكراً لك، تم استلام طلب دراسة ماكينة الباب بنجاح وسنتواصل معك قريباً.',
            'gate_machine_study' => $study,
        ], 201);
    }

    /** GET /api/admin/gate-machine-studies */
    public function adminIndex(Request $request)
    {
        $query = GateMachineStudyRequest::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    /** PATCH /api/admin/gate-machine-studies/{gateMachineStudy} */
    public function update(Request $request, GateMachineStudyRequest $gateMachineStudy)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,done',
        ]);

        $gateMachineStudy->update($validated);

        return response()->json($gateMachineStudy->fresh());
    }

    /** DELETE /api/admin/gate-machine-studies/{gateMachineStudy} */
    public function destroy(GateMachineStudyRequest $gateMachineStudy)
    {
        $gateMachineStudy->delete();

        return response()->json(['message' => 'تم حذف الطلب.']);
    }
}
