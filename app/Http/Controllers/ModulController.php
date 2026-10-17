<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateModulRequest;
use App\Http\Requests\UpdateModulRequest;
use App\Models\Modul;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Http\Request;

class ModulController extends Controller
{
    /**
     * @OA\Get(
     *     path="/modules/latest",
     *     tags={"Modules"},
     *     summary="Get latest 5 modules",
     *     @OA\Response(response=200, description="Latest modules retrieved successfully"),
     * )
     */
    public function getLatestModuls(): JsonResponse
    {
        $moduls = Modul::latest()->limit(5)->get();

        if ($moduls->isEmpty()) {
            return $this->error('No modules found', 404);
        }

        return $this->success($moduls, 'Latest modules retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/modules",
     *     tags={"Modules"},
     *     summary="Get all modules",
     *     @OA\Response(response=200, description="Modules retrieved successfully"),
     * )
     */
    public function index()
    {
        $modules = Modul::all();

        if ($modules->isEmpty()) {
            return $this->error('No modules found', 404);
        }

        return $this->success($modules, 'Modules retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/modules",
     *     tags={"Modules"},
     *     summary="Create a new module",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Module created successfully"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(CreateModulRequest $request): JsonResponse
    {
        $data = $request->validated();

        $modul = Modul::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        return $this->success($modul, 'Module created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/modules/{id}",
     *     tags={"Modules"},
     *     summary="Get module by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Module retrieved successfully"),
     *     @OA\Response(response=404, description="Module not found")
     * )
     */
    public function show($id): JsonResponse
    {
        $modul = Modul::find($id);

        if (!$modul) {
            return $this->error('Module not found', 404);
        }

        return $this->success($modul, 'Module retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/modules/{id}",
     *     tags={"Modules"},
     *     summary="Update module by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Module updated successfully"),
     *     @OA\Response(response=404, description="Module not found"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/modules/{id}",
     *     tags={"Modules"},
     *     summary="Delete module by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Module deleted successfully"),
     *     @OA\Response(response=404, description="Module not found")
     * )
     */
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
