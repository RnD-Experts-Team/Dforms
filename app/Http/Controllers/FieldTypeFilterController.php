<?php

namespace App\Http\Controllers;

use App\Services\FieldTypeFilterService;
use App\Http\Requests\FieldTypeFilter\StoreFieldTypeFilterRequest;
use App\Http\Requests\FieldTypeFilter\UpdateFieldTypeFilterRequest;
use Illuminate\Http\JsonResponse;

class FieldTypeFilterController extends Controller
{
    protected FieldTypeFilterService $fieldTypeFilterService;

    public function __construct(FieldTypeFilterService $fieldTypeFilterService)
    {
        $this->fieldTypeFilterService = $fieldTypeFilterService;
    }

    /**
     * Get all field type filters
     * GET /api/field-type-filters
     */
    public function index(): JsonResponse
    {
        try {
            $fieldTypeFilters = $this->fieldTypeFilterService->getAllFieldTypeFilters();

            return response()->json([
                'success' => true,
                'data' => $fieldTypeFilters,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve field type filters.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new field type filter
     * POST /api/field-type-filters
     */
    public function store(StoreFieldTypeFilterRequest $request): JsonResponse
    {
        try {
            $fieldTypeFilter = $this->fieldTypeFilterService->createFieldTypeFilter($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Field type filter created successfully.',
                'data' => $fieldTypeFilter->load('fieldType'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create field type filter.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific field type filter
     * GET /api/field-type-filters/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $fieldTypeFilter = $this->fieldTypeFilterService->getFieldTypeFilterById($id);

            return response()->json([
                'success' => true,
                'data' => $fieldTypeFilter,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field type filter not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update an existing field type filter
     * PUT /api/field-type-filters/{id}
     */
    public function update(UpdateFieldTypeFilterRequest $request, int $id): JsonResponse
    {
        try {
            $fieldTypeFilter = $this->fieldTypeFilterService->updateFieldTypeFilter($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Field type filter updated successfully.',
                'data' => $fieldTypeFilter,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field type filter.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a field type filter
     * DELETE /api/field-type-filters/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->fieldTypeFilterService->deleteFieldTypeFilter($id);

            return response()->json([
                'success' => true,
                'message' => 'Field type filter deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field type filter.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
