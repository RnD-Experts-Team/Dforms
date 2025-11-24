<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\Category\AssignFormsToCategoryRequest;
use App\Http\Requests\Category\UnassignFormsFromCategoryRequest;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all categories
     * GET /api/categories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getAllCategories();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new category
     * POST /api/categories
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific category
     * GET /api/categories/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update an existing category
     * PUT /api/categories/{id}
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->updateCategory($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a category
     * DELETE /api/categories/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully. Forms in this category are now without category.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign forms to a category (single or bulk)
     * POST /api/categories/assign-forms
     */
    public function assignForms(AssignFormsToCategoryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->categoryService->assignFormsToCategory($validated['category_id'], $validated['form_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Forms assigned to category successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign forms to category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unassign forms from their current category
     * POST /api/categories/unassign-forms
     */
    public function unassignForms(UnassignFormsFromCategoryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->categoryService->unassignFormsFromCategory($validated['form_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Forms unassigned from categories successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unassign forms from categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
