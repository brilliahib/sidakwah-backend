<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMaterialContentRequest;
use App\Http\Requests\UpdateMaterialContentRequest;
use App\Models\MaterialContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialContentController extends Controller
{
    public function getLatestMaterials(): JsonResponse
    {
        $materials = MaterialContent::latest()->limit(5)->get();

        if ($materials->isEmpty()) {
            return $this->error('No material contents found', 404);
        }

        return $this->success($materials, 'Latest material contents retrieved successfully');
    }

    public function index()
    {
        $materials = MaterialContent::all();

        if ($materials->isEmpty()) {
            return $this->error('No material contents found', 404);
        }

        return $this->success($materials, 'Material contents retrieved successfully');
    }

    public function getBySubModul($subModulId)
    {
        if (!$subModulId) {
            return $this->error('Sub-module ID is required', 400);
        }

        $materials = MaterialContent::where('sub_modul_id', $subModulId)->get();

        if ($materials->isEmpty()) {
            return $this->error('No material contents found for the specified sub-module', 404);
        }

        return $this->success($materials, 'Material contents for the specified sub-module retrieved successfully');
    }

    public function show($id)
    {
        $material = MaterialContent::find($id);

        if (!$material) {
            return $this->error('Material content not found', 404);
        }

        return $this->success($material, 'Material content retrieved successfully');
    }

    public function store(CreateMaterialContentRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('article_images')) {
            $subModulId = $data['sub_modul_id'];

            $path = $request->file('article_images')
                ->store("material-images/{$subModulId}", 'public');

            $data['article_images'] = $path;
        }

        $material = MaterialContent::create($data);

        return $this->success($material, 'Material content created successfully', 201);
    }

    public function update(UpdateMaterialContentRequest $request, $id)
    {
        $material = MaterialContent::find($id);

        if (!$material) {
            return $this->error('Material content not found', 404);
        }

        $data = $request->except('article_images');

        if ($request->hasFile('article_images')) {
            if ($material->article_images && Storage::disk('public')->exists($material->article_images)) {
                Storage::disk('public')->delete($material->article_images);
            }

            $path = $request->file('article_images')
                ->store("material-images/{$material->sub_modul_id}", 'public');

            $data['article_images'] = $path;
        }

        $material->update($data);

        return $this->success($material->fresh(), 'Material content updated successfully');
    }
}
