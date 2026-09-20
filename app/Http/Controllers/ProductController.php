<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, int $club): JsonResponse|View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $request->user()->can('viewAny', [Product::class, $clubModel]) || abort(Response::HTTP_FORBIDDEN);
        $products = $clubModel->products()
            ->withCount([
                'invoiceCreationLines as draft_lines_count' => fn ($query) => $query->whereHas(
                    'creation',
                    fn ($creationQuery) => $creationQuery->where('status', 'draft'),
                ),
            ])
            ->orderBy('name')
            ->get();

        return $request->expectsJson() ? response()->json(['data' => $products]) : view('products.index', ['club' => $clubModel, 'products' => $products]);
    }

    public function create(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        abort_unless($request->user()->can('create', [Product::class, $clubModel]), Response::HTTP_FORBIDDEN);

        return view('products.create', ['club' => $clubModel]);
    }

    public function store(StoreProductRequest $request, int $club): JsonResponse|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $product = $clubModel->products()->create($request->validated());

        return $request->expectsJson()
            ? response()->json(['data' => $product], Response::HTTP_CREATED)
            : redirect()->route('clubs.products.index', $clubModel);
    }

    public function show(Request $request, int $club, int $product): JsonResponse|View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $productModel = $clubModel->products()->findOrFail($product);
        abort_unless($request->user()->can('view', $productModel), Response::HTTP_FORBIDDEN);

        return $request->expectsJson() ? response()->json(['data' => $productModel]) : view('products.show', ['club' => $clubModel, 'product' => $productModel]);
    }

    public function edit(Request $request, int $club, int $product): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $productModel = $clubModel->products()->findOrFail($product);
        abort_unless($request->user()->can('update', $productModel), Response::HTTP_FORBIDDEN);

        return view('products.edit', ['club' => $clubModel, 'product' => $productModel]);
    }

    public function update(UpdateProductRequest $request, int $club, int $product): JsonResponse|RedirectResponse
    {
        $productModel = $request->user()->clubs()->findOrFail($club)->products()->findOrFail($product);
        $productModel->update($request->validated());

        return $request->expectsJson() ? response()->json(['data' => $productModel->fresh()]) : redirect()->route('clubs.products.index', $club);
    }

    public function destroy(Request $request, int $club, int $product): Response|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $productModel = $clubModel->products()->findOrFail($product);
        abort_unless($request->user()->can('delete', $productModel), Response::HTTP_FORBIDDEN);
        $productModel->delete();

        return $request->expectsJson() ? response()->noContent() : redirect()->route('clubs.products.index', $clubModel);
    }
}
