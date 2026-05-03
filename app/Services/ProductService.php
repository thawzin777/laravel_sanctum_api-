<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class ProductService
{
    // Get all products
    public function getAllProducts()
    {
        
        $products=DB::table('products')->orderBy('created_at', 'desc')->paginate(6);
        return $products;
    }

    // Get single product
    public function existProduct(string $name)
    {
        return DB::table('products')->where('name', $name)->exists();
    }

    // Create a new product
    public function createProduct(array $data)
    {
        $product=DB::table('products')->insert($data);
        return $product;
    }
}