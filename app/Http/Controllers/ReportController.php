<?php



namespace App\Http\Controllers;



use App\Models\AppointmentSlot;

use App\Models\Report;

use App\Support\ArabicPdfText;

use App\Support\BrowserPdf;

use App\Support\CompanySettings;

use App\Support\DompdfFontCache;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;



class ReportController extends Controller

{

    private const MAX_IMAGES = 5;



    /** GET /api/admin/reports */

    public function index(Request $request)

    {

        $query = Report::with('appointmentSlot')->orderByDesc('created_at');



        if ($request->filled('appointment_slot_id')) {

            $query->where('appointment_slot_id', $request->appointment_slot_id);

        }



        return response()->json($query->get());

    }



    /** GET /api/admin/reports/{report} */

    public function show(Report $report)

    {

        return response()->json($report->load('appointmentSlot'));

    }



    /** POST /api/admin/reports */

    public function store(Request $request)

    {

        $validated = $this->validated($request);



        if (!empty($validated['appointment_slot_id'])) {

            $this->applyAppointmentDefaults($validated, AppointmentSlot::find($validated['appointment_slot_id']));

        }



        $validated['images'] = $this->storeImages($request);

        $validated = $this->normalizeReportFields($validated);



        $report = Report::create($validated);



        return response()->json($report->fresh('appointmentSlot'), 201);

    }



    /** POST /api/admin/reports/{report} (multipart, _method=PUT) */

    public function update(Request $request, Report $report)

    {

        $validated = $this->validated($request);



        $existingImages = [];

        if ($request->filled('existing_images')) {

            $decoded = json_decode($request->input('existing_images'), true);

            $existingImages = is_array($decoded) ? $decoded : [];

        }



        $newImages = $this->storeImages($request);

        $allImages = array_slice(array_merge($existingImages, $newImages), 0, self::MAX_IMAGES);



        $removed = array_diff((array) $report->images, $allImages);

        foreach ($removed as $path) {

            $this->deleteImageFile($path);

        }



        unset($validated['existing_images'], $validated['images']);

        $validated['images'] = $allImages;

        $validated = $this->normalizeReportFields($validated);



        $report->update($validated);



        return response()->json($report->fresh('appointmentSlot'));

    }



    /** DELETE /api/admin/reports/{report} */

    public function destroy(Report $report)

    {

        foreach ((array) $report->images as $path) {

            $this->deleteImageFile($path);

        }



        $report->delete();



        return response()->json(['message' => 'تم حذف التقرير.']);

    }



    /** GET /api/admin/reports/{report}/html — browser preview (same layout as PDF) */

    public function html(Report $report)

    {

        return response()->view('reports.report-html', $this->reportViewData($report));

    }



    /** GET /api/admin/reports/{report}/pdf */

    public function pdf(Report $report)

    {

        DompdfFontCache::ensureReady();

        try {

            $data = $this->reportViewData($report);

            $filename = ($data['reportNo'] ?? 'report') . '.pdf';



            $rendered = BrowserPdf::render(view('reports.report-html', $data)->render());

            if ($rendered !== null) {

                return response($rendered, 200, [

                    'Content-Type' => 'application/pdf',

                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',

                    'Content-Length' => (string) strlen($rendered),

                ]);

            }



            $pdf = Pdf::loadView('reports.report-html', $data + ['forPdf' => true])->setPaper('a4', 'portrait');

            $pdf->setOption('isRemoteEnabled', true);



            return $pdf->download($filename);

        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error('Report PDF export failed', [

                'report_id' => $report->id,

                'error' => $e->getMessage(),

            ]);



            return response()->json([

                'message' => 'تعذر إنشاء ملف PDF: ' . $e->getMessage(),

            ], 500);

        }

    }



    /** @return array<string, mixed> */

    private function reportViewData(Report $report): array

    {

        $report->loadMissing('appointmentSlot');

        $slot = $report->appointmentSlot;



        $company = $this->companySettings();

        $logoPath = $this->absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');

        $icons = $this->reportIconDataUris();



        $imageDataUris = [];

        foreach ((array) $report->images as $path) {

            $absolute = $this->absoluteAssetPath($path);

            $dataUri = $this->toDataUri($absolute, 640);

            if ($dataUri) {

                $imageDataUris[] = $dataUri;

            }

        }

        while (count($imageDataUris) < self::MAX_IMAGES) {

            $imageDataUris[] = null;

        }



        $visitDate = $report->visit_date

            ? \Illuminate\Support\Carbon::parse($report->visit_date)

            : null;

        $visitDateLabel = $visitDate ? $visitDate->format('d / m / Y') : '— / — / —';



        $visitTime = $report->visit_time

            ?: ($slot?->start_time ? \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') : '—');



        $visitType = $report->visit_type

            ?: ($slot?->type === 'maintenance' ? 'زيارة صيانة' : 'زيارة دورية / متابعة');



        $contact = is_array($company['contact'] ?? null) ? $company['contact'] : [];

        $addressAr = is_array($contact['address'] ?? null)

            ? (($contact['address']['ar'] ?? null) ?: ($contact['address']['en'] ?? 'الإمارات العربية المتحدة'))

            : 'الإمارات العربية المتحدة';



        $clientName = $report->client_name ?: '—';

        $engineerName = $report->engineer_name ?: '—';

        $notesText = $report->report_notes ?: $report->content;



        $companyNameAr = preg_replace('/\x{0640}/u', '', (string) ($company['companyNameAr'] ?? $company['companyname_ar'] ?? 'التدفق الذكي'));
        $tagline = 'للأنظمة الذكية والحلول التقنية';
        if (str_contains($companyNameAr, $tagline)) {
            $companyNameAr = trim(str_replace($tagline, '', $companyNameAr));
        }



        return [

            'report' => $report,

            'logoDataUri' => $this->toDataUri($logoPath, 220),

            'companyNameAr' => $companyNameAr,

            'phone' => (string) ($contact['phone'] ?? '+971'),

            'email' => (string) ($contact['email'] ?? 'info@smartflow.ae'),

            'addressAr' => (string) $addressAr,

            'reportNo' => 'SV-' . ($visitDate?->format('Y') ?? date('Y')) . '-' . str_pad((string) $report->id, 4, '0', STR_PAD_LEFT),

            'visitDateLabel' => $visitDateLabel,

            'visitTimeLabel' => (string) $visitTime,

            'visitTypeLabel' => (string) $visitType,

            'recipientEntity' => (string) ($report->recipient_entity ?: $clientName),

            'siteAddress' => (string) ($report->site_address ?: ($slot?->location ?: '—')),

            'siteCompany' => (string) ($report->site_company ?: $clientName),

            'contactPhone' => (string) ($report->contact_phone ?: ($contact['phone'] ?? '—')),

            'deliveryMethod' => (string) ($report->delivery_method ?: 'زيارة ميدانية'),

            'deliveryNotes' => (string) ($report->delivery_notes ?: '—'),

            'executedWorksLines' => $this->bulletLines($report->executed_works, false),

            'notesLines' => $this->bulletLines($notesText, false),

            'recommendationsLines' => $this->bulletLines($report->recommendations, false),

            'engineerName' => $engineerName,

            'clientName' => $clientName,

            'imageDataUris' => $imageDataUris,

            'iconPhone' => $icons['phone'],

            'iconEmail' => $icons['email'],

            'iconLocation' => $icons['location'],

            'waveSvg' => $icons['wave'],

            'fontEmbedCss' => $this->documentFontEmbedCss(),

        ];

    }



    private function documentFontEmbedCss(): string

    {

        $faces = [

            ['Cairo', 400, 'Cairo-Regular.ttf'],

            ['Cairo', 700, 'Cairo-Bold.ttf'],

            ['Tajawal', 400, 'Tajawal-Regular.ttf'],

            ['Tajawal', 700, 'Tajawal-Bold.ttf'],

            ['CairoFallback', 400, 'NotoSansArabic-Regular.ttf'],

            ['CairoFallback', 700, 'NotoSansArabic-Bold.ttf'],

        ];



        $css = '';

        foreach ($faces as [$family, $weight, $file]) {

            $path = resource_path('fonts/' . $file);

            if (!is_file($path)) {

                continue;

            }



            $css .= sprintf(

                "@font-face{font-family:'%s';font-style:normal;font-weight:%d;font-display:block;"

                . "src:url(data:font/ttf;base64,%s) format('truetype');}\n",

                $family,

                $weight,

                base64_encode((string) file_get_contents($path))

            );

        }



        return $css;

    }



    /** @return array<string, mixed> */

    private function validated(Request $request): array

    {

        return $request->validate([

            'appointment_slot_id' => 'nullable|exists:appointment_slots,id',

            'title' => 'nullable|string|max:255',

            'content' => 'nullable|string|max:15000',

            'client_name' => 'nullable|string|max:255',

            'engineer_name' => 'nullable|string|max:255',

            'visit_date' => 'nullable|date',

            'visit_time' => 'nullable|string|max:50',

            'visit_type' => 'nullable|string|max:255',

            'recipient_entity' => 'nullable|string|max:500',

            'site_address' => 'nullable|string|max:1000',

            'site_company' => 'nullable|string|max:500',

            'contact_phone' => 'nullable|string|max:50',

            'delivery_method' => 'nullable|string|max:500',

            'delivery_notes' => 'nullable|string|max:2000',

            'executed_works' => 'nullable|string|max:8000',

            'report_notes' => 'nullable|string|max:8000',

            'recommendations' => 'nullable|string|max:8000',

            'existing_images' => 'nullable|string',

            'images' => 'nullable|array|max:' . self::MAX_IMAGES,

            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',

        ]);

    }



    /** @param array<string, mixed> $validated */

    private function applyAppointmentDefaults(array &$validated, ?AppointmentSlot $slot): void

    {

        if (!$slot) {

            return;

        }

        $validated['client_name'] = $validated['client_name'] ?? $slot->customer_name;

        $validated['engineer_name'] = $validated['engineer_name'] ?? $slot->engineer_name;

        $validated['visit_date'] = $validated['visit_date'] ?? $slot->date->format('Y-m-d');

        $validated['visit_time'] = $validated['visit_time'] ?? ($slot->start_time

            ? \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i')

            : null);

        $validated['site_address'] = $validated['site_address'] ?? $slot->location;

        $validated['recipient_entity'] = $validated['recipient_entity'] ?? $slot->customer_name;

    }



    /** @param array<string, mixed> $validated */

    private function normalizeReportFields(array $validated): array

    {

        if (empty($validated['report_notes']) && !empty($validated['content'])) {

            $validated['report_notes'] = $validated['content'];

        }

        if (empty($validated['content']) && !empty($validated['report_notes'])) {

            $validated['content'] = $validated['report_notes'];

        }



        return $validated;

    }



    /** @return array<int, string> */

    private function bulletLines(?string $text, bool $shapeArabic = true): array

    {

        if (!$text) {

            return [];

        }



        $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];

        if (count($lines) === 1 && mb_strlen((string) $lines[0]) > 100) {
            $parts = preg_split('/(?<=[.!?؟])\s+/u', (string) $lines[0]) ?: [];
            if (count($parts) > 1) {
                $lines = $parts;
            }
        }



        return array_values(array_filter(array_map(function ($line) use ($shapeArabic) {

            $line = trim((string) $line);

            $line = preg_replace('/^[-•*]\s*/u', '', $line) ?? $line;

            $line = preg_replace('/^\d+[.)]\s*/u', '', $line) ?? $line;

            $line = trim($line);



            if ($line === '') {

                return null;

            }



            return $shapeArabic ? ArabicPdfText::shape($line) : $line;

        }, $lines)));

    }



    /** @return array<string, string|null> */

    private function reportIconDataUris(): array

    {

        $blue = '#1a437f';

        $svg = static fn (string $body, int $w = 14, int $h = 14) => 'data:image/svg+xml;base64,' . base64_encode(

            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 24 24">' . $body . '</svg>'

        );



        $wavePath = resource_path('images/receipt/wave.png');

        $wave = is_file($wavePath)

            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($wavePath))

            : null;



        return [

            'phone' => $svg('<path fill="' . $blue . '" d="M6.6 10.8c1.5 2.9 3.7 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z"/>'),

            'email' => $svg('<path fill="' . $blue . '" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5z"/>'),

            'location' => $svg('<path fill="' . $blue . '" d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>'),

            'wave' => $wave,

        ];

    }



    private function companySettings(): array

    {

        return CompanySettings::read();

    }



    private function containsArabic(?string $text): bool

    {

        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', (string) $text);

    }



    private function toDataUri(?string $absolutePath, int $maxSide = 0): ?string

    {

        if (!$absolutePath || !File::exists($absolutePath)) {

            return null;

        }



        if (function_exists('imagecreatefromstring')) {

            $raw = @File::get($absolutePath);

            if ($raw === false || $raw === '') {

                return null;

            }



            $src = @imagecreatefromstring($raw);

            if ($src !== false) {

                $w = imagesx($src);

                $h = imagesy($src);



                if ($maxSide > 0 && ($w > $maxSide || $h > $maxSide)) {

                    $scale = min($maxSide / $w, $maxSide / $h);

                    $nw = max(1, (int) round($w * $scale));

                    $nh = max(1, (int) round($h * $scale));

                    $dst = imagecreatetruecolor($nw, $nh);

                    $white = imagecolorallocate($dst, 255, 255, 255);

                    imagefill($dst, 0, 0, $white);

                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

                    imagedestroy($src);

                    $src = $dst;

                }



                ob_start();

                imagejpeg($src, null, 82);

                $jpeg = ob_get_clean();

                imagedestroy($src);



                if ($jpeg) {

                    return 'data:image/jpeg;base64,' . base64_encode($jpeg);

                }

            }

        }



        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($ext === 'webp') {

            return null;

        }



        $mime = match ($ext) {

            'jpg', 'jpeg' => 'image/jpeg',

            'png' => 'image/png',

            'gif' => 'image/gif',

            default => 'image/jpeg',

        };



        return 'data:' . $mime . ';base64,' . base64_encode(File::get($absolutePath));

    }



    private function storeImages(Request $request): array

    {

        $paths = [];

        if (!$request->hasFile('images')) {

            return $paths;

        }



        $destinationPath = public_path('storage/reports');

        if (!File::isDirectory($destinationPath)) {

            File::makeDirectory($destinationPath, 0755, true);

        }



        foreach ($request->file('images') as $image) {

            if (!$image) {

                continue;

            }

            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            $image->move($destinationPath, $filename);

            $paths[] = '/storage/reports/' . $filename;

        }



        return $paths;

    }



    private function deleteImageFile(?string $webPath): void

    {

        if (!$webPath) {

            return;

        }

        $absolute = public_path(ltrim($webPath, '/'));

        if (File::exists($absolute)) {

            File::delete($absolute);

        }

    }



    private function absoluteAssetPath(?string $webPath): ?string

    {

        if (!$webPath) {

            return null;

        }

        if (preg_match('/^https?:\/\//i', $webPath)) {

            return $webPath;

        }

        $absolute = public_path(ltrim($webPath, '/'));



        return File::exists($absolute) ? $absolute : null;

    }

}


