<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::query()
            ->latest()
            ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:10485760', function ($attribute, $value, $fail) {
                if (! $this->isBase64Image($value)) {
                    $fail('The image must be a valid base64 image string.');
                }
            }],
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:10485760', function ($attribute, $value, $fail) {
                if (! $this->isBase64Image($value)) {
                    $fail('The image must be a valid base64 image string.');
                }
            }],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Deleted']);
    }

    private function isBase64Image(string $value): bool
    {
        $data = $value;

        if (str_starts_with($data, 'data:')) {
            $commaPosition = strpos($data, ',');
            if ($commaPosition === false) {
                return false;
            }
            $data = substr($data, $commaPosition + 1);
        }

        if ($data === '') {
            return false;
        }

        return base64_decode($data, true) !== false;
    }
}
