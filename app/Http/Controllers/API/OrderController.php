<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{
    //

    public function index(){
        if (!Gate::allows('only-admins')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.name as user_name')
            ->orderBy('orders.created_at', 'desc')
            ->get();
        return $this->sendResponse($orders, 'Orders retrieved successfully.');
    }

   public function store(Request $request)
{
    $validated = Validator::make($request->all(), [
        'items' => 'required|array',
        'items.*.product_id' => 'required|integer|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    if ($validated->fails()) {
        return $this->sendError('Validation Error.', $validated->errors(), 422);
    }

    DB::beginTransaction();

    try {

        $totalPrice = 0;

        // 1. Create order
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => auth()->id() ?? 1,
            'total_price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($validated->validated()['items'] as $item) {

            // ✔ use Eloquent (IMPORTANT FIX)
           // $product = Product::find($item['product_id']);
            $product=DB::table('products')->where('id', $item['product_id'])->first();
            if ($product->stock < $item['quantity']) {
                return $this->sendError('Insufficient stock for ' . $product->name, [], 400);
            }

            $price = $product->price;
            $subtotal = $price * $item['quantity'];

            // 2. insert order items
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. deduct stock
            $product->stock -= $item['quantity'];
            DB::table('products')->where('id', $product->id)->update(['stock' => $product->stock]);

            $totalPrice += $subtotal;
        }

        // // 4. update order total
        DB::table('orders')->where('id', $orderId)->update([
            'total_price' => $totalPrice,
            'updated_at' => now(),
        ]);

        DB::commit();

        return $this->sendResponse([
            'order_id' => $orderId,
            'total_price' => $totalPrice
        ], 'Order created successfully.');

    } catch (\Exception $e) {
        DB::rollBack();

        return $this->sendError('Failed: ' . $e->getMessage(), [], 500);
    }
}

   public function show ($id){
    if (!Gate::allows('only-admins')) {
        return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
    }
    $order = DB::table('orders')
        ->join('users', 'orders.user_id', '=', 'users.id')
        ->select('orders.*', 'users.name as user_name')
        ->where('orders.id', $id)
        ->first();

    if (!$order) {
        return $this->sendError('Order not found.', [], 404);
    }

    $items = DB::table('order_items')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->select('order_items.*', 'products.name as product_name')
        ->where('order_items.order_id', $id)
        ->get();

    $order->items = $items;
    
    return $this->sendResponse($order, 'Order retrieved successfully.');
   }
}
