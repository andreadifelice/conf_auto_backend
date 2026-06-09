<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()->orderBy('name')->get();

        return response()->json($categories);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load([
            'carModels' => fn ($query) => $query->where('is_active', true),
        ]);

        return response()->json($category);
    }
}
