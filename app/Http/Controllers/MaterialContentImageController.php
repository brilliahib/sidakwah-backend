<?php

namespace App\Http\Controllers;

use App\Models\MaterialContentRevision;
use Illuminate\Support\Facades\Storage;

class MaterialContentImageController extends Controller
{
    public function show(int $revisionId)
    {
        $revision = MaterialContentRevision::query()
            ->whereKey($revisionId)
            ->first();

        if (! $revision) {
            return $this->error('Material revision not found', 404);
        }

        if (! in_array(auth()->user()->role, ['admin', 'ustadz'], true)) {
            return $this->error('Forbidden. You do not have access to this resource.', 403);
        }

        $path = $revision->article_images;
        if (! $path || ! Storage::disk('material_revisions')->exists($path)) {
            return $this->error('Revision image not found', 404);
        }

        return response()->file(Storage::disk('material_revisions')->path($path));
    }
}
