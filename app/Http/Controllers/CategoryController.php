<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()->orderBy('name')->get();

        return response()->json($categories);
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        try {
            $category = Category::create($data);

            return response()->json([
                'message' => 'Categoria creata con successo',
                'data' => $category
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante la creazione della categoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Category $category): JsonResponse
    {
        $category->load([
            'carModels' => fn ($query) => $query->where('is_active', true),
        ]);

        return response()->json($category);
    }
}
