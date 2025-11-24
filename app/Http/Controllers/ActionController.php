<?php

namespace App\Http\Controllers;

use App\Services\ActionService;
use App\Http\Requests\Action\StoreActionRequest;
use App\Http\Requests\Action\UpdateActionRequest;
use Illuminate\Http\JsonResponse;

class ActionController extends Controller
{
    protected ActionService $actionService;

    public function __construct(ActionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Get all actions
     * GET /api/actions
     */
    public function index(): JsonResponse
    {
        try {
            $actions = $this->actionService->getAllActions();

            return response()->json([
                'success' => true,
                'data' => $actions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve actions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new action
     * POST /api/actions
     */
    public function store(StoreActionRequest $request): JsonResponse
    {
        try {
            $action = $this->actionService->createAction($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Action created successfully.',
                'data' => $action,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create action.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific action
     * GET /api/actions/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $action = $this->actionService->getActionById($id);

            return response()->json([
                'success' => true,
                'data' => $action,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Action not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update an existing action
     * PUT /api/actions/{id}
     */
    public function update(UpdateActionRequest $request, int $id): JsonResponse
    {
        try {
            $action = $this->actionService->updateAction($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Action updated successfully.',
                'data' => $action,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update action.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an action
     * DELETE /api/actions/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->actionService->deleteAction($id);

            return response()->json([
                'success' => true,
                'message' => 'Action deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete action.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
