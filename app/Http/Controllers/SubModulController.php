<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSubModulRequest;
use App\Http\Requests\UpdateSubModulRequest;
use App\Models\Modul;
use App\Models\SubModul;
use Illuminate\Http\Request;

class SubModulController extends Controller
{
    public function index()
    {
        $subModuls = SubModul::all();

        if ($subModuls->isEmpty()) {
            return $this->error('No sub-modules found', 404);
        }

        return $this->success($subModuls, 'Sub-modules retrieved successfully');
    }

    public function getSubModulesByModul($modulId)
    {
        if (!$modulId) {
            return $this->error('Module ID is required', 400);
        }

        $modul = Modul::find($modulId);

        if (!$modul) {
            return $this->error('Module not found', 404);
        }

        $subModuls = SubModul::where('modul_id', $modulId)->get();

        if ($subModuls->isEmpty()) {
            return $this->error('No sub-modules found for the specified module', 404);
        }

        return $this->success($subModuls, 'Sub-modules for the specified module retrieved successfully');
    }

    public function show($id)
    {
        $subModul = SubModul::find($id);

        if (!$subModul) {
            return $this->error('Sub-module not found', 404);
        }

        return $this->success($subModul, 'Sub-module retrieved successfully');
    }

    public function store(CreateSubModulRequest $request)
    {
        $data = $request->validated();
        $subModul = SubModul::create($data);

        return $this->success($subModul, 'Sub-module created successfully', 201);
    }

    public function update(UpdateSubModulRequest $request, $id)
    {
        $subModul = SubModul::find($id);

        if (!$subModul) {
            return $this->error('Sub-module not found', 404);
        }

        $data = $request->validated();
        $subModul->update($data);

        return $this->success($subModul, 'Sub-module updated successfully');
    }

    public function destroy($id)
    {
        $subModul = SubModul::find($id);

        if (!$subModul) {
            return $this->error('Sub-module not found', 404);
        }

        $subModul->delete();

        return $this->success(null, 'Sub-module deleted successfully');
    }
}
