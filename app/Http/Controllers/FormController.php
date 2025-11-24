<?php

namespace App\Http\Controllers;

use App\Services\FormService;
use App\Http\Requests\Form\GetFormsListRequest;
use App\Http\Requests\Form\StoreFormRequest;
use App\Http\Requests\Form\UpdateFormRequest;
use Illuminate\Http\JsonResponse;

class FormController extends Controller
{
    protected FormService $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * GET /api/forms
     */
    public function index(GetFormsListRequest $request): JsonResponse
    {
        try {
            $forms = $this->formService->getFormsList($request->validated());

            return response()->json([
                'success' => true,
                'data' => $forms->items(),
                'pagination' => [
                    'current_page' => $forms->currentPage(),
                    'last_page' => $forms->lastPage(),
                    'per_page' => $forms->perPage(),
                    'total' => $forms->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve forms list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/forms
     */
    public function store(StoreFormRequest $request): JsonResponse
    {
        try {
            $form = $this->formService->createForm($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Form created successfully with version 0, initial stage, and first section.',
                'data' => $form,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/forms/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $form = $this->formService->getFormById($id);

            return response()->json([
                'success' => true,
                'data' => $form,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * PUT /api/forms/{id}
     */
    public function update(UpdateFormRequest $request, int $id): JsonResponse
    {
        try {
            $form = $this->formService->updateForm($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Form updated successfully.',
                'data' => $form,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/forms/{id}/archive
     */
    public function archive(int $id): JsonResponse
    {
        try {
            $this->formService->archiveForm($id);

            return response()->json([
                'success' => true,
                'message' => 'Form and all its versions archived successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/forms/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $this->formService->restoreForm($id);

            return response()->json([
                'success' => true,
                'message' => 'Form restored successfully. Latest version set as published.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
