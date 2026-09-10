<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListProductsRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(ListProductsRequest $request): AnonymousResourceCollection
    {
        $filters = [
            'search' => $request->validated('search'),
            'category' => $request->validated('category'),
            'limit' => (int) $request->validated('limit', 10),
            'page' => (int) $request->validated('page', 1),
        ];
        $key = $this->cacheKey('list:'.hash('sha256', json_encode($filters)));
        $cached = Cache::remember($key, config('cache.product_ttl_seconds'), function () use ($filters) {
            $query = Product::query();

            if ($filters['search'] !== null) {
                $search = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], Str::lower($filters['search']));
                $query->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", ['%'.$search.'%']);
            }

            if ($filters['category'] !== null) {
                $query->where('category', $filters['category']);
            }

            $page = $query->orderByDesc('id')->paginate($filters['limit'], ['*'], 'page', $filters['page']);

            return [
                'items' => $page->getCollection()->map(fn (Product $product) => $product->getAttributes())->all(),
                'total' => $page->total(),
            ];
        });

        // Cache only attributes; pagination links must reflect the current request URL.
        $products = new LengthAwarePaginator(
            Product::hydrate($cached['items']),
            $cached['total'],
            $filters['limit'],
            $filters['page'],
            ['path' => $request->url(), 'query' => $request->safe()->only(['search', 'category', 'limit'])],
        );

        return ProductResource::collection($products);
    }

    public function show(string $product): ProductResource
    {
        $attributes = Cache::remember(
            $this->cacheKey('detail:'.$product),
            config('cache.product_ttl_seconds'),
            fn () => Product::findOrFail($product)->getAttributes(),
        );

        return new ProductResource((new Product)->newFromBuilder($attributes));
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = new Product($request->validated());
        $product->created_by = $request->user()->username;
        $product->created_by_id = $request->user()->id;
        $product->save();
        $this->invalidateCache();

        return (new ProductResource($product->refresh()))->response()->setStatusCode(201);
    }

    public function update(ProductRequest $request, Product $product): ProductResource
    {
        $product->fill($request->validated());
        $product->description = $request->validated('description');
        $product->updated_by = $request->user()->username;
        $product->updated_by_id = $request->user()->id;
        $product->save();
        $this->invalidateCache();

        return new ProductResource($product->refresh());
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        $this->invalidateCache();

        return response()->json(['message' => 'Product deleted successfully.', 'data' => null]);
    }

    private function cacheKey(string $suffix): string
    {
        return 'products:'.Cache::get('products:version', 'initial').':'.$suffix;
    }

    private function invalidateCache(): void
    {
        Cache::forever('products:version', (string) Str::uuid());
    }
}
