<?php

namespace App\Http\Controllers;

use App\Services\InputRuleService;
use App\Http\Requests\InputRule\StoreInputRuleRequest;
use App\Http\Requests\InputRule\UpdateInputRuleRequest;
use Illuminate\Http\JsonResponse;

class InputRuleController extends Controller
{
    protected InputRuleService $inputRuleService;

    public function __construct(InputRuleService $inputRuleService)
    {
        $this->inputRuleService = $inputRuleService;
    }

    /**
     * Get all input rules
     * GET /api/input-rules
     */
    public function index(): JsonResponse
    {
        try {
            $inputRules = $this->inputRuleService->getAllInputRules();

            return response()->json([
                'success' => true,
                'data' => $inputRules,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve input rules.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new input rule
     * POST /api/input-rules
     */
    public function store(StoreInputRuleRequest $request): JsonResponse
    {
        try {
            $inputRule = $this->inputRuleService->createInputRule($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Input rule created successfully.',
                'data' => $inputRule,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create input rule.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific input rule
     * GET /api/input-rules/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $inputRule = $this->inputRuleService->getInputRuleById($id);

            return response()->json([
                'success' => true,
                'data' => $inputRule,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Input rule not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update an existing input rule
     * PUT /api/input-rules/{id}
     */
    public function update(UpdateInputRuleRequest $request, int $id): JsonResponse
    {
        try {
            $inputRule = $this->inputRuleService->updateInputRule($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Input rule updated successfully.',
                'data' => $inputRule,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update input rule.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an input rule
     * DELETE /api/input-rules/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->inputRuleService->deleteInputRule($id);

            return response()->json([
                'success' => true,
                'message' => 'Input rule deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete input rule.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
