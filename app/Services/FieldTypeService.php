<?php

namespace App\Services;

use App\Models\FieldType;

class FieldTypeService
{
    /**
     * Get all field types
     */
    public function getAllFieldTypes()
    {
        return FieldType::orderBy('name', 'asc')->get();
    }

    /**
     * Create a new field type
     */
    public function createFieldType(array $data)
    {
        return FieldType::create($data);
    }

    /**
     * Get a specific field type by ID
     */
    public function getFieldTypeById(int $id)
    {
        return FieldType::findOrFail($id);
    }

    /**
     * Update an existing field type
     */
    public function updateFieldType(int $id, array $data)
    {
        $fieldType = FieldType::findOrFail($id);
        $fieldType->update($data);
        return $fieldType;
    }

    /**
     * Delete a field type
     */
    public function deleteFieldType(int $id)
    {
        $fieldType = FieldType::findOrFail($id);

        // Check if field type is being used by any fields
        $fieldsCount = $fieldType->fields()->count();
        if ($fieldsCount > 0) {
            throw new \Exception("Cannot delete this field type. It is currently used by {$fieldsCount} field(s) in forms.");
        }

        // Check if field type has input rules associated
        $rulesCount = $fieldType->inputRules()->count();
        if ($rulesCount > 0) {
            // Detach all input rules before deletion
            $fieldType->inputRules()->detach();
        }

        // Check if field type has filters
        $filtersCount = $fieldType->fieldTypeFilters()->count();
        if ($filtersCount > 0) {
            throw new \Exception("Cannot delete this field type. It has {$filtersCount} filter(s) associated with it. Please delete the filters first.");
        }

        $fieldType->delete();

        return true;
    }
}
