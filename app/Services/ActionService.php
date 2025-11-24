<?php

namespace App\Services;

use App\Models\Action;

class ActionService
{
    /**
     * Get all actions
     */
    public function getAllActions()
    {
        return Action::orderBy('name', 'asc')->get();
    }

    /**
     * Create a new action
     */
    public function createAction(array $data)
    {
        // Set is_public default if not provided
        if (!isset($data['is_public'])) {
            $data['is_public'] = false;
        }

        return Action::create($data);
    }

    /**
     * Get a specific action by ID
     */
    public function getActionById(int $id)
    {
        return Action::findOrFail($id);
    }

    /**
     * Update an existing action
     */
    public function updateAction(int $id, array $data)
    {
        $action = Action::findOrFail($id);
        $action->update($data);
        return $action;
    }

    /**
     * Delete an action
     */
    public function deleteAction(int $id)
    {
        $action = Action::findOrFail($id);

        // Check if action is being used by any stage transition actions
        $usageCount = $action->stageTransitionActions()->count();
        if ($usageCount > 0) {
            throw new \Exception("Cannot delete this action. It is currently used by {$usageCount} stage transition(s) in forms.");
        }

        $action->delete();

        return true;
    }
}
