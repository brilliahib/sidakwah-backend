<?php

namespace App\Http\Controllers;

use App\Models\MaterialContentRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialContentReviewController extends Controller
{
    public function index()
    {
        $revisions = MaterialContentRevision::query()
            ->with(['materialContent.subModul:id,title', 'subModul:id,title', 'submitter:id,name'])
            ->where('status', MaterialContentRevision::STATUS_PENDING)
            ->orderBy('submitted_at')
            ->get();

        return $this->success($revisions, 'Pending material revisions retrieved successfully');
    }

    public function show(int $revisionId)
    {
        $revision = MaterialContentRevision::with([
            'materialContent.subModul:id,title,modul_id',
            'materialContent.subModul.modul:id,title',
            'submitter:id,name',
        ])->find($revisionId);

        if (! $revision) {
            return $this->error('Material revision not found', 404);
        }

        return $this->success($revision, 'Material revision retrieved successfully');
    }

    public function decide(Request $request, int $revisionId)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'reason' => 'required_if:decision,rejected|nullable|string|max:5000',
        ]);

        $result = DB::transaction(function () use ($request, $revisionId, $validated) {
            $revision = MaterialContentRevision::query()
                ->whereKey($revisionId)
                ->lockForUpdate()
                ->first();

            if (! $revision) {
                return ['error' => 'Material revision not found', 'code' => 404];
            }

            $currentVersion = MaterialContentRevision::query()
                ->where('material_content_id', $revision->material_content_id)
                ->max('version');

            if ($revision->status !== MaterialContentRevision::STATUS_PENDING || $currentVersion !== $revision->version) {
                return ['error' => 'This revision is no longer pending review.', 'code' => 409];
            }

            $revision->status = $validated['decision'];
            $revision->reviewed_by = $request->user()->id;
            $revision->reviewed_at = now();
            $revision->rejection_reason = $validated['decision'] === 'rejected'
                ? trim($validated['reason'])
                : null;
            $revision->save();

            if ($validated['decision'] === 'approved') {
                $material = $revision->materialContent()->lockForUpdate()->firstOrFail();
                $newImage = $revision->article_images;
                if ($newImage) {
                    $publicPath = "material-images/{$revision->sub_modul_id}/".basename($revision->article_images);
                    $imageContents = Storage::disk('material_revisions')->get($revision->article_images);
                    if (! Storage::disk('public')->put($publicPath, $imageContents)) {
                        throw new \RuntimeException('Unable to publish the reviewed material image.');
                    }
                    $newImage = $publicPath;
                }

                $material->update([
                    'sub_modul_id' => $revision->sub_modul_id,
                    'title' => $revision->title,
                    'youtube_link' => $revision->youtube_link,
                    'article_title' => $revision->article_title,
                    'article_content' => $revision->article_content,
                    'article_images' => $newImage,
                    'published_at' => now(),
                ]);
            }

            return ['revision' => $revision->fresh()->load(['reviewer:id,name', 'materialContent'])];
        });

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }

        return $this->success($result['revision'], 'Material revision decision saved successfully');
    }
}
