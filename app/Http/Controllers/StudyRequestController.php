<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSlot;
use App\Models\StudyRequest;
use Illuminate\Http\Request;

class StudyRequestController extends Controller
{
    /** POST /api/study-requests — public submission (استبيان دراسة المشروع) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_status' => 'required|in:under_construction,existing',
            'systems' => 'nullable|array',
            'systems.*' => 'string',
            'systems_other' => 'nullable|string|max:255',
            'plans' => 'nullable|array',
            'plans.*' => 'string',
            'plan_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:15360',
            'infrastructure_by' => 'nullable|in:contractor,company',
            'proposed_system' => 'nullable|in:wired,wireless',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'project_location' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'appointment_slot_id' => 'nullable|exists:appointment_slots,id',
        ]);

        $slot = null;
        if (!empty($validated['appointment_slot_id'])) {
            $slot = AppointmentSlot::find($validated['appointment_slot_id']);

            if (!$slot) {
                return response()->json(['message' => 'هذا الموعد لم يعد متاحاً، يرجى اختيار موعد آخر.'], 422);
            }

            // Allow linking a slot the same customer already booked (e.g. via the
            // "confirm via WhatsApp" button), otherwise block if someone else booked it.
            $alreadyBookedBySameCustomer = $slot->status === 'booked'
                && $slot->customer_phone === $validated['customer_phone'];

            if ($slot->status === 'booked' && !$alreadyBookedBySameCustomer) {
                return response()->json(['message' => 'هذا الموعد لم يعد متاحاً، يرجى اختيار موعد آخر.'], 422);
            }
        }

        $planFiles = [];
        if ($request->hasFile('plan_files')) {
            $dest = public_path('storage/study-plans');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            foreach ($request->file('plan_files') as $file) {
                $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($dest, $filename);
                $planFiles[] = '/storage/study-plans/' . $filename;
            }
        }

        $studyRequest = StudyRequest::create([
            'house_status' => $validated['house_status'],
            'systems' => $validated['systems'] ?? [],
            'systems_other' => $validated['systems_other'] ?? null,
            'plans' => $validated['plans'] ?? [],
            'plan_files' => $planFiles,
            'infrastructure_by' => $validated['infrastructure_by'] ?? null,
            'proposed_system' => $validated['proposed_system'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'project_location' => $validated['project_location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'appointment_slot_id' => $slot?->id,
            'status' => 'new',
        ]);

        if ($slot && $slot->status !== 'booked') {
            $slot->update([
                'status' => 'booked',
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
            ]);
        }

        return response()->json([
            'message' => 'شكراً لك، تم استلام طلبك بنجاح وسنتواصل معك قريباً.',
            'study_request' => $studyRequest->fresh('appointmentSlot'),
        ], 201);
    }

    /** GET /api/admin/study-requests */
    public function adminIndex(Request $request)
    {
        $query = StudyRequest::with('appointmentSlot')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    /** GET /api/admin/study-requests/{studyRequest} */
    public function show(StudyRequest $studyRequest)
    {
        return response()->json($studyRequest->load('appointmentSlot'));
    }

    /** PATCH /api/admin/study-requests/{studyRequest} */
    public function update(Request $request, StudyRequest $studyRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,done',
        ]);

        $studyRequest->update($validated);

        return response()->json($studyRequest->fresh('appointmentSlot'));
    }

    /** DELETE /api/admin/study-requests/{studyRequest} */
    public function destroy(StudyRequest $studyRequest)
    {
        $studyRequest->delete();

        return response()->json(['message' => 'تم حذف الطلب.']);
    }
}
