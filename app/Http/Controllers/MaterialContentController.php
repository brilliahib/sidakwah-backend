<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMaterialContentRequest;
use App\Http\Requests\UpdateMaterialContentRequest;
use App\Models\MaterialContent;
use App\Models\MaterialContentRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialContentController extends Controller
{
    public function getLatestMaterials(): JsonResponse
    {
        $materials = MaterialContent::published()->with([
            'subModul:id,title,modul_id',
            'subModul.modul:id,title',
        ])->latest()->limit(5)->get();

        if ($materials->isEmpty()) {
            return $this->error('No material contents found', 404);
        }

        return $this->success($materials, 'Latest material contents retrieved successfully');
    }

    public function index()
    {
        $materials = MaterialContent::with([
            'subModul:id,title,modul_id',
            'subModul.modul:id,title',
            'latestRevision',
        ])->latest()->get();

        return $this->success($materials, 'Material contents retrieved successfully');
    }

    public function getBySubModul($subModulId)
    {
        $materials = MaterialContent::published()
            ->with(['subModul:id,title,modul_id', 'subModul.modul:id,title'])
            ->where('sub_modul_id', $subModulId)
            ->get();

        if ($materials->isEmpty()) {
            return $this->error('No material contents found for the specified sub-module', 404);
        }

        return $this->success($materials, 'Material contents for the specified sub-module retrieved successfully');
    }

    public function show($id)
    {
        $material = MaterialContent::published()->with([
            'subModul:id,title,modul_id',
            'subModul.modul:id,title',
        ])->find($id);

        if (! $material) {
            return $this->error('Material content not found', 404);
        }

        return $this->success($material, 'Material content retrieved successfully');
    }

    public function store(CreateMaterialContentRequest $request)
    {
        $data = $request->validated();
        $action = $data['action'] ?? 'save_draft';
        unset($data['action']);
        $imagePath = $request->hasFile('article_images')
            ? $request->file('article_images')->store('material-revisions', 'material_revisions')
            : null;

        $material = DB::transaction(function () use ($data, $action, $imagePath, $request) {
            $material = MaterialContent::create([
                'sub_modul_id' => null,
                'title' => null,
                'published_at' => null,
            ]);

            $revision = $material->revisions()->create([
                ...$data,
                'article_images' => $imagePath,
                'version' => 1,
                'status' => $action === 'submit'
                    ? MaterialContentRevision::STATUS_PENDING
                    : MaterialContentRevision::STATUS_DRAFT,
                'submitted_by' => $request->user()->id,
                'submitted_at' => $action === 'submit' ? now() : null,
            ]);

            return $material->load(['latestRevision', 'subModul']);
        });

        return $this->success($material, 'Material content saved successfully', 201);
    }

    public function update(UpdateMaterialContentRequest $request, $id)
    {
        $material = MaterialContent::with('latestRevision')->find($id);
        if (! $material) {
            return $this->error('Material content not found', 404);
        }

        $data = $request->validated();
        $action = $data['action'] ?? 'submit';
        unset($data['action']);
        $latest = $material->latestRevision;
        $base = $latest ?? $material;
        $snapshot = [
            'sub_modul_id' => $data['sub_modul_id'] ?? $base->sub_modul_id,
            'title' => $data['title'] ?? $base->title,
            'youtube_link' => $data['youtube_link'] ?? $base->youtube_link,
            'article_title' => $data['article_title'] ?? $base->article_title,
            'article_content' => $data['article_content'] ?? $base->article_content,
            'article_images' => $base->article_images,
        ];

        if ($request->hasFile('article_images')) {
            $snapshot['article_images'] = $request->file('article_images')
                ->store("material-revisions/{$material->id}", 'material_revisions');
        } elseif (! $latest && $material->article_images && Storage::disk('public')->exists($material->article_images)) {
            $privatePath = "material-revisions/{$material->id}/".basename($material->article_images);
            Storage::disk('public')->copy($material->article_images, $privatePath);
            $snapshot['article_images'] = $privatePath;
        }

        $result = DB::transaction(function () use ($material, $latest, $snapshot, $action, $request) {
            $isDraft = $latest?->status === MaterialContentRevision::STATUS_DRAFT;
            if ($isDraft) {
                $revision = $latest;
                $revision->fill($snapshot);
            } else {
                $revision = $material->revisions()->make([
                    ...$snapshot,
                    'version' => ($latest?->version ?? 0) + 1,
                    'submitted_by' => $request->user()->id,
                ]);
            }

            $revision->status = $action === 'submit'
                ? MaterialContentRevision::STATUS_PENDING
                : MaterialContentRevision::STATUS_DRAFT;
            $revision->submitted_by = $request->user()->id;
            $revision->submitted_at = $action === 'submit' ? now() : null;
            $revision->reviewed_by = null;
            $revision->reviewed_at = null;
            $revision->rejection_reason = null;
            $revision->save();

            if ($action === 'submit' || ! $isDraft) {
                $material->revisions()
                    ->where('id', '!=', $revision->id)
                    ->where('status', MaterialContentRevision::STATUS_PENDING)
                    ->update(['status' => MaterialContentRevision::STATUS_SUPERSEDED]);
            }

            return $material->fresh()->load(['latestRevision', 'subModul']);
        });

        return $this->success($result, 'Material content revision saved successfully');
    }

    public function destroy($id)
    {
        $material = MaterialContent::with('revisions')->find($id);
        if (! $material) {
            return $this->error('Material content not found', 404);
        }

        foreach ($material->revisions as $revision) {
            if ($revision->article_images) {
                Storage::disk('material_revisions')->delete($revision->article_images);
            }
        }
        if ($material->article_images) {
            Storage::disk('public')->delete($material->article_images);
        }

        $material->delete();

        return $this->success(null, 'Material content deleted successfully');
    }
}
