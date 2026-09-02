<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectContact;
use App\Models\ProjectFile;
use App\Models\Quotation;
use App\Support\ProjectQr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    /** Public portfolio list (frontend) */
    public function index()
    {
        $projects = Project::query()
            ->where('is_public', true)
            ->whereIn('status', ['in_progress', 'completed'])
            ->with(['publicFiles'])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Project $p) => $this->publicShape($p));

        return response()->json($projects);
    }

    /** Public single project */
    public function show(Project $project)
    {
        if (!$project->is_public) {
            abort(404);
        }

        $project->load(['publicFiles']);

        return response()->json($this->publicShape($project));
    }

    /** Admin list with filters */
    public function adminIndex(Request $request)
    {
        $query = Project::query()
            ->with(['customer:id,name,phone,email'])
            ->withCount(['quotations', 'invoices']);

        $tab = $request->query('tab', 'active');
        if ($tab === 'completed') {
            $query->where('status', 'completed');
        } else {
            $query->whereIn('status', ['draft', 'in_progress', 'on_hold']);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        return response()->json(
            $query->orderByDesc('updated_at')->get()
        );
    }

    /** Admin detail */
    public function adminShow(Project $project)
    {
        $project->load([
            'customer',
            'contacts',
            'files',
            'quotations' => fn ($q) => $q->orderByDesc('date')->limit(50),
            'invoices' => fn ($q) => $q->orderByDesc('date')->limit(50)->with([
                'quotation.items' => fn ($iq) => $iq->orderBy('sort_order')->orderBy('id'),
            ]),
            'payments',
            'expenses',
            'deliveryNotes',
            'profitShares',
        ]);

        $data = $project->toArray();
        $data['finance'] = $project->finance_summary;
        $data['expense_presets'] = \App\Models\ProjectExpense::PRESETS;
        $data['payment_types'] = \App\Models\ProjectPayment::TYPES;

        return response()->json($data);
    }

    public function linkOptions(Project $project)
    {
        $project->load('customer:id,name');

        return response()->json([
            'invoices' => $this->customerInvoicesForProject($project),
            'quotations' => $this->customerQuotationsForProject($project),
            'linked_invoice_id' => Invoice::where('project_id', $project->id)->value('id'),
            'linked_quotation_id' => Quotation::where('project_id', $project->id)->value('id'),
        ]);
    }

    public function linkInvoice(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::with('quotation:id,customer_id,client_name')->findOrFail($data['invoice_id']);
        $project->load('customer');
        $this->assertDocumentMatchesProjectCustomer(
            $project,
            $invoice->client_name,
            $invoice->quotation?->customer_id
        );

        DB::transaction(function () use ($project, $invoice) {
            Invoice::where('project_id', $project->id)->update(['project_id' => null]);
            $invoice->update(['project_id' => $project->id]);

            if ($invoice->quotation_id) {
                Quotation::where('project_id', $project->id)->update(['project_id' => null]);
                Quotation::where('id', $invoice->quotation_id)->update(['project_id' => $project->id]);
            }
        });

        return response()->json($this->adminProjectPayload($project->fresh()));
    }

    public function linkQuotation(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
        ]);

        $quotation = Quotation::findOrFail($data['quotation_id']);
        $project->load('customer');
        $this->assertDocumentMatchesProjectCustomer($project, $quotation->client_name, $quotation->customer_id, 'quotation_id');

        DB::transaction(function () use ($project, $quotation) {
            Quotation::where('project_id', $project->id)->update(['project_id' => null]);
            $quotation->update(['project_id' => $project->id]);
        });

        return response()->json($this->adminProjectPayload($project->fresh()));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        return DB::transaction(function () use ($request, $validated) {
            $project = Project::create($validated);
            $this->syncContacts($project, $request->input('contacts'));
            $this->syncFiles($project, $request);
            $this->refreshQr($project);

            return response()->json($project->fresh(['customer', 'contacts', 'files']), 201);
        });
    }

    public function update(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $validated = $this->validated($request, $project->id);

        return DB::transaction(function () use ($request, $project, $validated) {
            if (($validated['status'] ?? null) === 'completed') {
                $validated['completed_at'] = now();
            }

            $project->update($validated);
            $this->syncContacts($project, $request->input('contacts'));
            $this->syncFiles($project, $request);
            $this->refreshQr($project);

            return response()->json($project->fresh(['customer', 'contacts', 'files', 'quotations', 'invoices']));
        });
    }

    public function destroy(Project $project)
    {
        $this->deleteProjectAssets($project);
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'required|string',
            'location' => 'nullable|string|max:500',
            'maps_url' => 'nullable|string|max:2000',
            'status' => ['nullable', Rule::in(Project::STATUSES)],
            'is_public' => 'nullable',
            'media_type' => 'nullable|in:image,video,text',
            'media_url' => 'nullable|string',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi',
            'order' => 'nullable|integer',
            'is_featured' => 'nullable',
            'contacts' => 'nullable',
            'capital_amount' => 'nullable|numeric|min:0',
        ]);

        $data['is_public'] = filter_var($data['is_public'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['is_featured'] = filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['status'] = $data['status'] ?? 'in_progress';
        $data['media_type'] = $data['media_type'] ?? 'image';

        if (array_key_exists('customer_id', $data) && ($data['customer_id'] === '' || $data['customer_id'] === null)) {
            $data['customer_id'] = null;
        }

        if (array_key_exists('capital_amount', $data) && ($data['capital_amount'] === '' || $data['capital_amount'] === null)) {
            $data['capital_amount'] = null;
        }

        if (!$data['maps_url'] && !empty($data['location'])) {
            $data['maps_url'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($data['location']);
        }

        return $data;
    }

    private function syncContacts(Project $project, mixed $raw): void
    {
        $rows = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($rows)) {
            return;
        }

        $project->contacts()->delete();
        foreach (array_values($rows) as $i => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            ProjectContact::create([
                'project_id' => $project->id,
                'name' => $name,
                'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
                'sort_order' => $i,
            ]);
        }
    }

    private function syncFiles(Project $project, Request $request): void
    {
        $keepIds = json_decode((string) $request->input('keep_file_ids', '[]'), true);
        $keepIds = is_array($keepIds) ? array_map('intval', $keepIds) : [];

        $toDelete = $project->files()->when($keepIds, fn ($q) => $q->whereNotIn('id', $keepIds))->get();
        foreach ($toDelete as $file) {
            $this->unlinkStoragePath($file->path);
            $file->delete();
        }

        $this->storeUploadedFiles($project, $request, 'public_files', 'public_labels', 'public');
        $this->storeUploadedFiles($project, $request, 'private_files', 'private_labels', 'private');

        // Legacy gallery → first public image if no public files yet
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $path = $this->moveUpload($image, 'projects');
                if ($path) {
                    ProjectFile::create([
                        'project_id' => $project->id,
                        'label' => 'صورة المشروع ' . ($i + 1),
                        'path' => $path,
                        'visibility' => 'public',
                        'kind' => 'image',
                        'sort_order' => 100 + $i,
                    ]);
                }
            }
        }

        if ($request->hasFile('media_file')) {
            $path = $this->moveUpload($request->file('media_file'), 'projects');
            if ($path) {
                $project->update(['media_url' => $path]);
            }
        }
    }

    private function storeUploadedFiles(Project $project, Request $request, string $filesKey, string $labelsKey, string $visibility): void
    {
        $files = $request->file($filesKey);

        // Some clients send public_files[0] / public_files[] — normalize to a list
        if ($files === null) {
            return;
        }
        if (! is_array($files)) {
            $files = [$files];
        }

        $labels = $request->input($labelsKey, []);
        if (! is_array($labels)) {
            $labels = [];
        }
        // Re-index so labels align when keys are 0,1,2...
        $files = array_values(array_filter($files));
        $labels = array_values($labels);

        $baseOrder = (int) $project->files()->where('visibility', $visibility)->max('sort_order');

        foreach ($files as $i => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            // Read metadata BEFORE move() — temp path is gone after move
            $originalName = $file->getClientOriginalName();
            $mime = (string) ($file->getMimeType() ?? '');
            $label = trim((string) ($labels[$i] ?? '')) ?: $originalName;
            $kind = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');

            $path = $this->moveUpload($file, 'projects/files');
            if (! $path) {
                continue;
            }

            ProjectFile::create([
                'project_id' => $project->id,
                'label' => mb_substr($label, 0, 255),
                'path' => $path,
                'visibility' => $visibility,
                'kind' => $kind,
                'sort_order' => $baseOrder + $i + 1,
            ]);
        }
    }

    private function moveUpload($file, string $folder): ?string
    {
        $filename = time() . '-' . uniqid() . '-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $destination = public_path('storage/' . $folder);
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $file->move($destination, $filename);

        return '/storage/' . $folder . '/' . $filename;
    }

    private function refreshQr(Project $project): void
    {
        $project->load(['customer', 'contacts']);
        $path = ProjectQr::generateAndSave($project);
        if ($path) {
            $project->update(['qr_path' => $path]);
        }
    }

    private function publicShape(Project $project): array
    {
        $cover = $project->publicFiles->firstWhere('kind', 'image')?->path
            ?? ($project->images[0] ?? null)
            ?? $project->media_url;

        return [
            'id' => $project->id,
            'title' => $project->title,
            'title_ar' => $project->title_ar,
            'description' => $project->description,
            'description_ar' => $project->description_ar,
            'location' => $project->location,
            'is_featured' => $project->is_featured,
            'order' => $project->order,
            'cover' => $cover,
            'media_type' => $project->media_type,
            'media_url' => $project->media_url,
            'files' => $project->publicFiles->map(fn (ProjectFile $f) => [
                'id' => $f->id,
                'label' => $f->label,
                'path' => $f->path,
                'kind' => $f->kind,
            ])->values(),
        ];
    }

    private function deleteProjectAssets(Project $project): void
    {
        if ($project->media_url) {
            $this->unlinkStoragePath($project->media_url);
        }
        if ($project->qr_path) {
            $this->unlinkStoragePath($project->qr_path);
        }
        if (is_array($project->images)) {
            foreach ($project->images as $img) {
                $this->unlinkStoragePath($img);
            }
        }
        foreach ($project->files as $file) {
            $this->unlinkStoragePath($file->path);
        }
    }

    private function unlinkStoragePath(?string $webPath): void
    {
        if (!$webPath || !str_starts_with($webPath, '/storage/')) {
            return;
        }
        $absolute = public_path(str_replace('/storage/', 'storage/', $webPath));
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    private function adminProjectPayload(Project $project): array
    {
        $project->load([
            'customer',
            'contacts',
            'files',
            'quotations' => fn ($q) => $q->orderByDesc('date')->limit(50),
            'invoices' => fn ($q) => $q->orderByDesc('date')->limit(50)->with([
                'quotation.items' => fn ($iq) => $iq->orderBy('sort_order')->orderBy('id'),
            ]),
            'payments',
            'expenses',
            'deliveryNotes',
            'profitShares',
        ]);

        $data = $project->toArray();
        $data['finance'] = $project->finance_summary;
        $data['expense_presets'] = \App\Models\ProjectExpense::PRESETS;
        $data['payment_types'] = \App\Models\ProjectPayment::TYPES;

        return $data;
    }

    private function customerInvoicesForProject(Project $project)
    {
        $customerId = $project->customer_id;
        $customerName = trim((string) ($project->customer?->name ?? ''));

        return Invoice::query()
            ->with('quotation:id,number,customer_id')
            ->where('status', '!=', 'cancelled')
            ->when($customerId || $customerName !== '', function ($q) use ($customerId, $customerName) {
                $q->where(function ($w) use ($customerId, $customerName) {
                    if ($customerId) {
                        $w->whereHas('quotation', fn ($qq) => $qq->where('customer_id', $customerId));
                    }
                    if ($customerName !== '') {
                        $w->orWhere('client_name', $customerName);
                    }
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'number', 'date', 'total', 'amount', 'client_name', 'project_id', 'quotation_id', 'status']);
    }

    private function customerQuotationsForProject(Project $project)
    {
        $customerId = $project->customer_id;

        return Quotation::query()
            ->where('status', '!=', 'cancelled')
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'number', 'date', 'total', 'client_name', 'project_id', 'status']);
    }

    private function assertDocumentMatchesProjectCustomer(Project $project, ?string $clientName, ?int $customerId, string $field = 'invoice_id'): void
    {
        if (!$project->customer_id) {
            throw ValidationException::withMessages([
                'customer_id' => ['اختر عميلاً للمشروع أولاً.'],
            ]);
        }

        if ($customerId && (int) $customerId !== (int) $project->customer_id) {
            throw ValidationException::withMessages([
                $field => ['هذه الوثيقة لا تتبع عميل المشروع.'],
            ]);
        }

        $projectCustomerName = trim((string) ($project->customer?->name ?? ''));
        if (!$customerId && $projectCustomerName !== '' && trim((string) $clientName) !== $projectCustomerName) {
            throw ValidationException::withMessages([
                $field => ['اسم العميل في الوثيقة لا يطابق عميل المشروع.'],
            ]);
        }
    }
}
