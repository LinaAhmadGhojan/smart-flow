<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSlot;
use App\Models\Customer;
use App\Models\Engineer;
use App\Models\StudyRequest;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    private const DAY_START = '09:00';
    private const DAY_END = '19:00';

    public function __construct(private WhatsAppService $whatsapp)
    {
    }

    /** GET /api/appointments — public list (status only, no customer data) */
    public function index(Request $request)
    {
        $query = AppointmentSlot::query();

        if ($request->filled('month')) {
            $parts = explode('-', $request->string('month'));
            [$year, $month] = [$parts[0] ?? null, $parts[1] ?? null];
            if ($year && $month) {
                $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
            }
        } elseif ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        } else {
            $query->where('date', '>=', now()->toDateString());
        }

        $slots = $query->orderBy('date')->orderBy('start_time')
            ->get(['id', 'date', 'start_time', 'end_time', 'status']);

        return response()->json($slots);
    }

    /** GET /api/admin/appointments — admin, full details */
    public function adminIndex(Request $request)
    {
        $query = AppointmentSlot::with('studyRequest')->orderBy('date')->orderBy('start_time');

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        } elseif ($request->filled('month')) {
            $parts = explode('-', $request->string('month'));
            [$year, $month] = [$parts[0] ?? null, $parts[1] ?? null];
            if ($year && $month) {
                $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    /** GET /api/admin/appointments/contacts — suggestions for autocomplete */
    public function contacts()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'email']);
        $engineers = Engineer::orderBy('name')->get(['id', 'name', 'phone', 'email']);

        return response()->json([
            'customers' => $customers,
            'engineers' => $engineers,
        ]);
    }

    /** Keep the customers/engineers directory in sync with names typed in the appointment form. */
    private function syncContact(string $modelClass, ?string $name, ?string $phone, ?string $email = null): void
    {
        $name = trim((string) $name);
        if ($name === '') {
            return;
        }

        $query = $modelClass::where('name', $name);
        if ($phone) {
            $query->where(function ($q) use ($phone) {
                $q->where('phone', $phone)->orWhereNull('phone')->orWhere('phone', '');
            });
        }

        $existing = $query->first();

        if ($existing) {
            $existing->fill(array_filter([
                'phone' => $phone ?: $existing->phone,
                'email' => $email ?: $existing->email,
            ]));
            $existing->save();
            return;
        }

        $modelClass::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
        ]);
    }

    /** Build the Arabic appointment confirmation message (customer + engineer share the same text). */
    private function buildAppointmentMessage(AppointmentSlot $slot): string
    {
        $typeLabel = $slot->type === 'maintenance' ? 'صيانة' : 'زيارة';
        $date = $slot->date instanceof \Illuminate\Support\Carbon
            ? $slot->date->translatedFormat('l j F Y')
            : $slot->date;
        $start = substr($slot->start_time, 0, 5);
        $end = $slot->end_time ? substr($slot->end_time, 0, 5) : null;
        $timeRange = $end ? "{$start} - {$end}" : $start;

        $message = 'مرحباً' . ($slot->customer_name ? ' ' . $slot->customer_name : '')
            . "، هذا تأكيد لموعد {$typeLabel} يوم {$date} الساعة {$timeRange}.";

        if ($slot->engineer_name) {
            $message .= " سيقوم بالزيارة المهندس {$slot->engineer_name}.";
        }
        if ($slot->location) {
            $message .= " الموقع: {$slot->location}.";
        }

        return $message . ' فريق SmartFlow.';
    }

    /** Send a WhatsApp confirmation to the customer and engineer (no-op if not configured). */
    private function notifyAppointment(AppointmentSlot $slot): void
    {
        $message = $this->buildAppointmentMessage($slot);

        if ($slot->customer_phone) {
            $this->whatsapp->send($slot->customer_phone, $message);
        }
        if ($slot->engineer_phone) {
            $this->whatsapp->send($slot->engineer_phone, $message);
        }
    }

    /**
     * Ensure the given time range is within working hours and doesn't overlap
     * with another appointment on the same date.
     */
    private function assertValidTimeRange(string $date, string $startTime, string $endTime, ?int $ignoreId = null): void
    {
        if ($startTime >= $endTime) {
            throw ValidationException::withMessages([
                'end_time' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
            ]);
        }

        if ($startTime < self::DAY_START || $endTime > self::DAY_END) {
            throw ValidationException::withMessages([
                'start_time' => 'المواعيد متاحة فقط من الساعة 9 صباحاً حتى 7 مساءً.',
            ]);
        }

        $overlap = AppointmentSlot::where('date', $date)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_time', '<', $endTime)
            ->where(function ($q) use ($startTime) {
                $q->whereNull('end_time')->orWhere('end_time', '>', $startTime);
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => 'هذا الوقت متداخل مع موعد آخر في نفس اليوم.',
            ]);
        }
    }

    /** POST /api/admin/appointments — create a new appointment */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'engineer_name' => 'nullable|string|max:255',
            'engineer_phone' => 'nullable|string|max:50',
            'type' => 'nullable|in:visit,maintenance',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['date'] < now()->toDateString()) {
            throw ValidationException::withMessages([
                'date' => 'لا يمكن إضافة موعد في تاريخ سابق. يرجى اختيار اليوم أو تاريخ لاحق.',
            ]);
        }

        $this->assertValidTimeRange($validated['date'], $validated['start_time'], $validated['end_time']);

        $slot = AppointmentSlot::create(array_merge($validated, [
            'status' => 'booked',
            'type' => $validated['type'] ?? 'visit',
        ]));

        $this->syncContact(Customer::class, $validated['customer_name'] ?? null, $validated['customer_phone'] ?? null, $validated['customer_email'] ?? null);
        $this->syncContact(Engineer::class, $validated['engineer_name'] ?? null, $validated['engineer_phone'] ?? null);
        $this->notifyAppointment($slot);

        return response()->json($slot->fresh('studyRequest'), 201);
    }

    /** PATCH /api/admin/appointments/{slot} */
    public function update(Request $request, AppointmentSlot $slot)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:available,booked',
            'date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'engineer_name' => 'nullable|string|max:255',
            'engineer_phone' => 'nullable|string|max:50',
            'type' => 'nullable|in:visit,maintenance',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (($validated['status'] ?? null) === 'available') {
            $validated['customer_name'] = null;
            $validated['customer_phone'] = null;
            $validated['customer_email'] = null;
        }

        $date = $validated['date'] ?? $slot->date->format('Y-m-d');
        $startTime = $validated['start_time'] ?? $slot->start_time;
        $endTime = $validated['end_time'] ?? $slot->end_time;

        if ($startTime && $endTime) {
            $this->assertValidTimeRange($date, $startTime, $endTime, $slot->id);
        }

        $slot->update($validated);

        if (($validated['status'] ?? null) !== 'available') {
            $this->syncContact(Customer::class, $slot->customer_name, $slot->customer_phone, $slot->customer_email);
            $this->syncContact(Engineer::class, $slot->engineer_name, $slot->engineer_phone);
            $this->notifyAppointment($slot);
        }

        return response()->json($slot->fresh('studyRequest'));
    }

    /** DELETE /api/admin/appointments/{slot} */
    public function destroy(AppointmentSlot $slot)
    {
        $slot->delete();

        return response()->json(['message' => 'تم حذف الموعد.']);
    }

    /** POST /api/appointments/{slot}/book — public quick booking (no full survey) */
    public function book(Request $request, AppointmentSlot $slot)
    {
        if ($slot->status === 'booked') {
            return response()->json(['message' => 'هذا الموعد محجوز بالفعل، يرجى اختيار موعد آخر.'], 422);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $slot->update(array_merge($validated, ['status' => 'booked']));

        $this->syncContact(Customer::class, $slot->customer_name, $slot->customer_phone, $slot->customer_email);
        $this->notifyAppointment($slot);

        return response()->json($slot->fresh());
    }
}
