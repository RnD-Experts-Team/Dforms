<?php

namespace App\Services;

use App\Models\FieldTypeFilter;

class FieldTypeFilterService
{
    /**
     * Get all field type filters with their associated field types
     */
    public function getAllFieldTypeFilters()
    {
        return FieldTypeFilter::with('fieldType')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Create a new field type filter
     */
    public function createFieldTypeFilter(array $data)
    {
        // Check if this field type already has a filter
        $existingFilter = FieldTypeFilter::where('field_type_id', $data['field_type_id'])->first();
        
        if ($existingFilter) {
            throw new \Exception('This field type already has a filter defined. Please update the existing filter instead.');
        }

        return FieldTypeFilter::create($data);
    }

    /**
     * Get a specific field type filter by ID
     */
    public function getFieldTypeFilterById(int $id)
    {
        return FieldTypeFilter::with('fieldType')->findOrFail($id);
    }

    /**
     * Update an existing field type filter
     */
    public function updateFieldTypeFilter(int $id, array $data)
    {
        $fieldTypeFilter = FieldTypeFilter::findOrFail($id);

        // If changing field type, check if the new field type already has a filter
        if (isset($data['field_type_id']) && $data['field_type_id'] != $fieldTypeFilter->field_type_id) {
            $existingFilter = FieldTypeFilter::where('field_type_id', $data['field_type_id'])->first();
            
            if ($existingFilter) {
                throw new \Exception('The target field type already has a filter defined.');
            }
        }

        $fieldTypeFilter->update($data);
        
        return $fieldTypeFilter->load('fieldType');
    }

    /**
     * Delete a field type filter
     */
    public function deleteFieldTypeFilter(int $id)
    {
        $fieldTypeFilter = FieldTypeFilter::findOrFail($id);
        $fieldTypeFilter->delete();

        return true;
    }

    /**
     * Get filter by field type ID
     */
    public function getFilterByFieldTypeId(int $fieldTypeId)
    {
        return FieldTypeFilter::with('fieldType')
            ->where('field_type_id', $fieldTypeId)
            ->first();
    }
}
