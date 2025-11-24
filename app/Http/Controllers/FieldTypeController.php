<?php

namespace App\Http\Controllers;

use App\Services\FieldTypeService;
use App\Http\Requests\FieldType\StoreFieldTypeRequest;
use App\Http\Requests\FieldType\UpdateFieldTypeRequest;
use Illuminate\Http\JsonResponse;

class FieldTypeController extends Controller
{
    protected FieldTypeService $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    /**
     * Get all field types
     * GET /api/field-types
     */
    public function index(): JsonResponse
    {
        try {
            $fieldTypes = $this->fieldTypeService->getAllFieldTypes();

            return response()->json([
                'success' => true,
                'data' => $fieldTypes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve field types.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new field type
     * POST /api/field-types
     */
    public function store(StoreFieldTypeRequest $request): JsonResponse
    {
        try {
            $fieldType = $this->fieldTypeService->createFieldType($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Field type created successfully.',
                'data' => $fieldType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create field type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific field type
     * GET /api/field-types/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $fieldType = $this->fieldTypeService->getFieldTypeById($id);

            return response()->json([
                'success' => true,
                'data' => $fieldType,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field type not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update an existing field type
     * PUT /api/field-types/{id}
     */
    public function update(UpdateFieldTypeRequest $request, int $id): JsonResponse
    {
        try {
            $fieldType = $this->fieldTypeService->updateFieldType($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Field type updated successfully.',
                'data' => $fieldType,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a field type
     * DELETE /api/field-types/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->fieldTypeService->deleteFieldType($id);

            return response()->json([
                'success' => true,
                'message' => 'Field type deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
