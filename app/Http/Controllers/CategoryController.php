<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Category/Index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validatedData = $request->validated();

        $category = Category::create($validatedData);

        return response()->json([
            'message' => 'Category created successfully!',
            'category' => $category // Optionally return the created category data
        ], 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category = Category::findOrFail($category->id);

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
