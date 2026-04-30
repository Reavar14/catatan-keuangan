<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'user_id' => $request->user()->id,
            'name'    => $request->name,
            'type'    => $request->type,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return response()->json([
            'data' => new CategoryResource($category),
        ], 200);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => new CategoryResource($category),
        ], 200);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        // Cek apakah kategori masih digunakan oleh transaksi
        if ($category->transactions()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh transaksi.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ], 200);
    }
}
