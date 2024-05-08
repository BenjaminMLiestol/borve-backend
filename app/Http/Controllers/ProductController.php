<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // If product_id is sent in the request, retrieve only that product
        if ($request->has('product_id')) {
            $product = Product::where('product_id', $request->input('product_id'))->first();
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }
            return response()->json($product, 200);
        }

        // Otherwise, retrieve all products
        $products = Product::all();

        if ($products->count() > 1) {
            $productList = $products->map(function ($product) {
                return [
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'is_service' => $product->is_service,
                    'url' => $product->url,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });
    
            return response()->json([
                'products' => $productList,
            ], 200);
        } else {
            return response()->json($products->first(), 200);
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'is_service' => 'required|boolean',
            'url' => 'nullable',
        ]);

         // Generate a unique product_id
        $validatedData['product_id'] = Str::slug(Str::random(15));

        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'is_service' => 'required|boolean',
            'url' => 'nullable',
        ]);

        $product->update($validatedData);

        return response()->json($product, 200);
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.'], 200);
    }
}
