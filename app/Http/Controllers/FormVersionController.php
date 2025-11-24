<?php

namespace App\Http\Controllers;

use App\Services\FormVersionService;
use App\Http\Requests\FormVersion\StoreFormVersionRequest;
use App\Http\Requests\FormVersion\UpdateFormVersionRequest;
use App\Http\Requests\FormVersion\PublishFormVersionRequest;
use Illuminate\Http\JsonResponse;

class FormVersionController extends Controller
{
    protected FormVersionService $formVersionService;

    public function __construct(FormVersionService $formVersionService)
    {
        $this->formVersionService = $formVersionService;
    }

    /**
     * GET /api/forms/{formId}/versions
     */
    public function index(int $formId): JsonResponse
    {
        try {
            $versions = $this->formVersionService->getFormVersions($formId);

            return response()->json([
                'success' => true,
                'data' => $versions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form versions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/forms/{formId}/versions
     */
    public function store(StoreFormVersionRequest $request, int $formId): JsonResponse
    {
        try {
            $copyFromCurrent = $request->input('copy_from_current');
            $version = $this->formVersionService->createNewVersion($formId, $copyFromCurrent);

            $message = $copyFromCurrent 
                ? 'New form version created successfully from current version.'
                : 'New blank form version created successfully with initial stage and section.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $version,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create new form version.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/form-versions/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $version = $this->formVersionService->getFormVersionById($id);

            return response()->json([
                'success' => true,
                'data' => $version,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form version not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * PUT /api/form-versions/{id}
     */
    public function update(UpdateFormVersionRequest $request, int $id): JsonResponse
    {
        try {
            $version = $this->formVersionService->updateFormVersion($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Form version updated successfully.',
                'data' => $version,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form version.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/form-versions/{id}/publish
     */
    public function publish(PublishFormVersionRequest $request, int $id): JsonResponse
    {
        try {
            $version = $this->formVersionService->publishFormVersion($id);

            return response()->json([
                'success' => true,
                'message' => 'Form version published successfully.',
                'data' => $version,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish form version.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
