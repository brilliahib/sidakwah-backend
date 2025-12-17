<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateModulRequest;
use App\Http\Requests\UpdateModulRequest;
use App\Models\Modul;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModulController extends Controller
{
    public function getLatestModuls(): JsonResponse
    {
        $moduls = Modul::latest()->limit(5)->get();

        return $this->success($moduls, 'Latest modules retrieved successfully');
    }

    public function index()
    {
        $modules = Modul::all();

        return $this->success($modules, 'Modules retrieved successfully');
    }

    public function store(CreateModulRequest $request): JsonResponse
    {
        $data = $request->validated();

        $modul = Modul::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        return $this->success($modul, 'Module created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $modul = Modul::find($id);

        if (!$modul) {
            return $this->error('Module not found', 404);
        }

        return $this->success($modul, 'Module retrieved successfully');
    }

    public function update(UpdateModulRequest $request, $id): JsonResponse
    {
        $modul = Modul::find($id);

        if (!$modul) {
            return $this->error('Module not found', 404);
        }

        $data = $request->validated();

        $modul->update($data);

        return $this->success($modul, 'Module updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $modul = Modul::find($id);

        if (!$modul) {
            return $this->error('Module not found', 404);
        }

        $modul->delete();

        return $this->success(null, 'Module deleted successfully');
    }
}
