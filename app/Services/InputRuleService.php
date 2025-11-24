<?php

namespace App\Services;

use App\Models\InputRule;
use Illuminate\Support\Facades\DB;

class InputRuleService
{
    /**
     * Get all input rules with their associated field types
     */
    public function getAllInputRules()
    {
        return InputRule::with('fieldTypes')->orderBy('name', 'asc')->get();
    }

    /**
     * Create a new input rule with field type associations
     */
    public function createInputRule(array $data)
    {
        DB::beginTransaction();

        try {
            $fieldTypeIds = $data['field_type_ids'];
            unset($data['field_type_ids']);

            // Set is_public default if not provided
            if (!isset($data['is_public'])) {
                $data['is_public'] = false;
            }

            $inputRule = InputRule::create($data);

            // Attach field types
            $inputRule->fieldTypes()->attach($fieldTypeIds);

            DB::commit();

            return $inputRule->load('fieldTypes');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get a specific input rule by ID
     */
    public function getInputRuleById(int $id)
    {
        return InputRule::with('fieldTypes')->findOrFail($id);
    }

    /**
     * Update an existing input rule
     */
    public function updateInputRule(int $id, array $data)
    {
        DB::beginTransaction();

        try {
            $inputRule = InputRule::findOrFail($id);

            $fieldTypeIds = null;
            if (isset($data['field_type_ids'])) {
                $fieldTypeIds = $data['field_type_ids'];
                unset($data['field_type_ids']);
            }

            // Update basic attributes
            $inputRule->update($data);

            // Sync field types if provided
            if ($fieldTypeIds !== null) {
                $inputRule->fieldTypes()->sync($fieldTypeIds);
            }

            DB::commit();

            return $inputRule->load('fieldTypes');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an input rule
     */
    public function deleteInputRule(int $id)
    {
        DB::beginTransaction();

        try {
            $inputRule = InputRule::findOrFail($id);

            // Check if input rule is being used by any field rules
            $fieldRulesCount = $inputRule->fieldRules()->count();
            if ($fieldRulesCount > 0) {
                throw new \Exception("Cannot delete this input rule. It is currently used by {$fieldRulesCount} field(s) in forms.");
            }

            // Detach all field type associations
            $inputRule->fieldTypes()->detach();

            // Delete the input rule
            $inputRule->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
