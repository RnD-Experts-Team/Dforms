<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Form;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data)
    {
        return Category::create($data);
    }

    /**
     * Get a specific category by ID
     */
    public function getCategoryById(int $id)
    {
        return Category::findOrFail($id);
    }

    /**
     * Update an existing category
     */
    public function updateCategory(int $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category
     * When deleted, forms in this category become "without category"
     */
    public function deleteCategory(int $id)
    {
        DB::beginTransaction();

        try {
            $category = Category::findOrFail($id);

            // Set all forms in this category to null (without category)
            Form::where('category_id', $id)->update(['category_id' => null]);

            // Delete the category
            $category->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign forms to a category (single or bulk)
     */
    public function assignFormsToCategory(int $categoryId, array $formIds)
    {
        // Verify category exists
        Category::findOrFail($categoryId);

        // Update all specified forms to belong to this category
        Form::whereIn('id', $formIds)->update(['category_id' => $categoryId]);

        return true;
    }

    /**
     * Unassign forms from their current category
     * Makes forms "without category"
     */
    public function unassignFormsFromCategory(array $formIds)
    {
        // Set category_id to null for specified forms
        Form::whereIn('id', $formIds)->update(['category_id' => null]);

        return true;
    }
}
