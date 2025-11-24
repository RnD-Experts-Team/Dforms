<?php

namespace App\Http\Controllers;

use App\Services\EntryService;
use App\Http\Requests\Entry\GetEntriesListRequest;
use App\Http\Requests\Entry\ToggleConsideredRequest;
use Illuminate\Http\JsonResponse;

class EntryController extends Controller
{
    protected EntryService $entryService;

    public function __construct(EntryService $entryService)
    {
        $this->entryService = $entryService;
    }

    /**
     * GET /api/entries
     * Get paginated entries list for a form version with filters
     */
    public function index(GetEntriesListRequest $request): JsonResponse
    {
        try {
            $entries = $this->entryService->getEntriesList($request->validated());

            return response()->json([
                'success' => true,
                'data' => $entries->items(),
                'pagination' => [
                    'current_page' => $entries->currentPage(),
                    'last_page' => $entries->lastPage(),
                    'per_page' => $entries->perPage(),
                    'total' => $entries->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve entries list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/entries/{id}
     * Get full entry details with form structure and values
     */
    public function show(int $id): JsonResponse
    {
        try {
            $entry = $this->entryService->getEntryById($id);

            return response()->json([
                'success' => true,
                'data' => $entry,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Entry not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * GET /api/entries/public/{publicIdentifier}
     * Get entry by public identifier (UUID/slug)
     */
    public function showByPublicIdentifier(string $publicIdentifier): JsonResponse
    {
        try {
            $entry = $this->entryService->getEntryByPublicIdentifier($publicIdentifier);

            return response()->json([
                'success' => true,
                'data' => $entry,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Entry not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * POST /api/entries/toggle-considered
     * Toggle considered status for single or bulk entries
     */
    public function toggleConsidered(ToggleConsideredRequest $request): JsonResponse
    {
        try {
            $this->entryService->toggleConsidered(
                $request->input('entry_ids'),
                $request->input('is_considered')
            );

            $message = $request->input('is_considered')
                ? 'Entries marked as considered successfully.'
                : 'Entries marked as unconsidered successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle considered status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
