<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            }

            //pagination
            $perPage = $request->get('per_page', 5);
            $categories = $query->paginate($perPage);

            return response()->json(['message' => 'Categories retrieved successfully', 'data' => CategoryResource::collection($categories), 'total' => $categories->total(), 'per_page' => $categories->perPage(), 'current_page' => $categories->currentPage(), 'last_page' => $categories->lastPage()], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        try {
            $category = Category::create($request->validated());
            return response()->json(['message' => 'Category created successfully', 'data' => CategoryResource::make($category)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return response()->json(['message' => 'Category retrieved successfully', 'data' => CategoryResource::make($category)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            $category->update($request->validated());
            return response()->json(['message' => 'Category updated successfully', 'data' => CategoryResource::make($category)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category  = Category::find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }
}
