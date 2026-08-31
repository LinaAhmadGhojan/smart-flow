<?php

namespace App\Http\Controllers;

use App\Models\ProjectMaster;
use App\Models\ProjectMasterFile;
use App\Support\StorageUrl;
use Illuminate\Http\Request;

class ProjectMasterController extends Controller
{
    /** Public portfolio list (website homepage) */
    public function index()
    {
        $items = ProjectMaster::query()
            ->visibleOnSite()
            ->with(['files'])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ProjectMaster $m) => $this->publicShape($m));

        return response()->json($items);
    }

    /** Public single portfolio item */
    public function show(ProjectMaster $projectMaster)
    {
        if (!$projectMaster->is_visible) {
            abort(404);
        }

        $projectMaster->load(['files']);

        return response()->json($this->publicShape($projectMaster));
    }

    /** Admin list */
    public function adminIndex(Request $request)
    {
        $query = ProjectMaster::query()->withCount('files');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->query('visible') !== null && $request->query('visible') !== '') {
            $query->where('is_visible', filter_var($request->query('visible'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(
            $query->orderBy('order')->orderByDesc('updated_at')->get()
        );
    }

    /** Admin detail */
    public function adminShow(ProjectMaster $projectMaster)
    {
        $projectMaster->load(['files']);

        return response()->json($projectMaster);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $master = ProjectMaster::create($data);
        $this->syncFiles($master, $request);

        return response()->json($master->fresh(['files']), 201);
    }

    public function update(Request $request, ProjectMaster $projectMaster)
    {
        $data = $this->validated($request);
        $projectMaster->update($data);
        $this->syncFiles($projectMaster, $request);

        return response()->json($projectMaster->fresh(['files']));
    }

    public function destroy(ProjectMaster $projectMaster)
    {
        $this->deleteAssets($projectMaster);
        $projectMaster->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    public function toggleVisibility(Request $request, ProjectMaster $projectMaster)
    {
        $projectMaster->update([
            'is_visible' => filter_var(
                $request->input('is_visible', !$projectMaster->is_visible),
                FILTER_VALIDATE_BOOLEAN
            ),
        ]);

        return response()->json($projectMaster);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'location' => 'nullable|string|max:500',
            'maps_url' => 'nullable|string|max:2000',
            'media_type' => 'nullable|in:image,video,text',
            'media_url' => 'nullable|string',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi|max:20480',
            'order' => 'nullable|integer',
            'is_featured' => 'nullable',
            'is_visible' => 'nullable',
        ]);

        $data['is_visible'] = filter_var($data['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $data['is_featured'] = filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['media_type'] = $data['media_type'] ?? 'image';
        $data['description'] = $data['description'] ?? '';
        $data['description_ar'] = $data['description_ar'] ?? '';
        $data['location'] = $data['location'] ?? null;
        $data['maps_url'] = $data['maps_url'] ?? null;
        $data['order'] = $data['order'] ?? 0;

        if (empty($data['maps_url']) && !empty($data['location'])) {
            $data['maps_url'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($data['location']);
        }

        unset($data['media_file']);

        return $data;
    }

    private function syncFiles(ProjectMaster $master, Request $request): void
    {
        $keepIds = json_decode((string) $request->input('keep_file_ids', '[]'), true);
        $keepIds = is_array($keepIds) ? array_map('intval', $keepIds) : [];

        $toDelete = $master->files()->when($keepIds, fn ($q) => $q->whereNotIn('id', $keepIds))->get();
        foreach ($toDelete as $file) {
            $this->unlinkStoragePath($file->path);
            $file->delete();
        }

        $this->storeUploadedFiles($master, $request, 'files', 'file_labels');

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $path = $this->moveUpload($image, 'project-masters');
                if ($path) {
                    ProjectMasterFile::create([
                        'project_master_id' => $master->id,
                        'label' => 'صورة ' . ($i + 1),
                        'path' => $path,
                        'kind' => 'image',
                        'sort_order' => 100 + $i,
                    ]);
                }
            }
        }

        if ($request->hasFile('media_file')) {
            $path = $this->moveUpload($request->file('media_file'), 'project-masters');
            if ($path) {
                $master->update(['media_url' => $path]);
            }
        }
    }

    private function storeUploadedFiles(ProjectMaster $master, Request $request, string $filesKey, string $labelsKey): void
    {
        $files = $request->file($filesKey);
        if ($files === null) {
            return;
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        $labels = $request->input($labelsKey, []);
        if (!is_array($labels)) {
            $labels = [];
        }

        $files = array_values(array_filter($files));
        $labels = array_values($labels);
        $baseOrder = (int) $master->files()->max('sort_order');

        foreach ($files as $i => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $mime = (string) ($file->getMimeType() ?? '');
            $label = trim((string) ($labels[$i] ?? '')) ?: $originalName;
            $kind = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');

            $path = $this->moveUpload($file, 'project-masters/files');
            if (!$path) {
                continue;
            }

            ProjectMasterFile::create([
                'project_master_id' => $master->id,
                'label' => mb_substr($label, 0, 255),
                'path' => $path,
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

    private function publicShape(ProjectMaster $master): array
    {
        $cover = $master->files->firstWhere('kind', 'image')?->path
            ?? ($master->images[0] ?? null)
            ?? $master->getRawOriginal('media_url');

        return [
            'id' => $master->id,
            'title' => $master->title,
            'title_ar' => $master->title_ar,
            'description' => $master->description,
            'description_ar' => $master->description_ar,
            'location' => $master->location,
            'is_featured' => $master->is_featured,
            'order' => $master->order,
            'cover' => StorageUrl::toPublicUrl($cover),
            'media_type' => $master->media_type,
            'media_url' => StorageUrl::toPublicUrl($master->getRawOriginal('media_url')),
            'files' => $master->files->map(fn (ProjectMasterFile $f) => [
                'id' => $f->id,
                'label' => $f->label,
                'path' => StorageUrl::toPublicUrl($f->path),
                'kind' => $f->kind,
            ])->values(),
        ];
    }

    private function deleteAssets(ProjectMaster $master): void
    {
        if ($master->media_url) {
            $this->unlinkStoragePath($master->media_url);
        }
        if (is_array($master->images)) {
            foreach ($master->images as $img) {
                $this->unlinkStoragePath($img);
            }
        }
        foreach ($master->files as $file) {
            $this->unlinkStoragePath($file->path);
        }
    }

    private function unlinkStoragePath(?string $path): void
    {
        if (!$path) {
            return;
        }
        $relative = ltrim(str_replace('/storage/', '', parse_url($path, PHP_URL_PATH) ?? $path), '/');
        $full = public_path('storage/' . $relative);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
