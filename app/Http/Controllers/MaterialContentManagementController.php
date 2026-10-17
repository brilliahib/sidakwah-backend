<?php

namespace App\Http\Controllers;

use App\Models\MaterialContent;
use App\Models\MaterialContentRevision;
use Illuminate\Http\Request;

class MaterialContentManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = MaterialContent::with([
            'subModul:id,title,modul_id',
            'latestRevision.subModul:id,title,modul_id',
        ])->latest();

        if ($status === 'published') {
            $query->whereNotNull('published_at');
        } elseif (in_array($status, [
            MaterialContentRevision::STATUS_DRAFT,
            MaterialContentRevision::STATUS_PENDING,
            MaterialContentRevision::STATUS_REJECTED,
            MaterialContentRevision::STATUS_APPROVED,
        ], true)) {
            $query->whereHas('latestRevision', fn ($revisions) => $revisions->where('status', $status));
        } elseif ($status !== null && $status !== 'superseded') {
            return $this->error('Invalid material status filter', 422);
        } elseif ($status === 'superseded') {
            $query->whereHas('revisions', fn ($revisions) => $revisions->where('status', $status));
        }

        $materials = $query->get()->map(function (MaterialContent $material) {
            $revision = $material->latestRevision;
            $material->setAttribute('management_status', $revision?->status ?? ($material->published_at ? 'published' : 'draft'));
            $material->setAttribute('has_published_version', $material->published_at !== null);
            return $material;
        });

        return $this->success($materials, 'Managed material contents retrieved successfully');
    }

    public function show(int $id)
    {
        $material = MaterialContent::with([
            'subModul:id,title,modul_id',
            'subModul.modul:id,title',
            'latestRevision.subModul:id,title,modul_id',
            'revisions.subModul:id,title,modul_id',
            'revisions.submitter:id,name',
            'revisions.reviewer:id,name',
        ])->find($id);

        if (! $material) {
            return $this->error('Material content not found', 404);
        }

        return $this->success($material, 'Managed material content retrieved successfully');
    }
}
