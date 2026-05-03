<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Services\ProductService;
class PostController extends BaseController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = $this->productService->getAllProducts();
        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated=Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'price'=>'required|numeric',
            'stock'=>'required|integer',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validated->fails()) {
            return $this->sendError('Validation Error.', $validated->errors());
        }
        $validatedData = $validated->validated();
        

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_images', 'public');
        }
        $validatedData['image'] = $imagePath;
        $existingProduct = $this->productService->existProduct($validatedData['name']);
        if ($existingProduct) {
            return $this->sendError('Product already exists.', [], 409);
        }
        $product = $this->productService->createProduct($validatedData);

        return $this->sendResponse($product, 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
