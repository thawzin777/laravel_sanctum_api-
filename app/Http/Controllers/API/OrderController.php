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
    
    $validatedData = $validated->validated();
    
    try{
        DB::transaction(function () use($validatedData) {
            
            // Find existing order for today
            $existOrder = DB::table('orders')
                ->where('user_id', auth()->id())
                ->whereDate('created_at', today())
                ->first();
                
            if(!$existOrder){
                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => auth()->id(),
                    'total_price' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $orderId = $existOrder->id;
            }
            
            foreach ($validatedData['items'] as $item) {
                // Get product
                $product = DB::table('products')->where('id', $item['product_id'])->first();
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for ' . $product->name);
                }
                
                $price = $product->price;
                
                // Check if order item already exists
                $existOrderItem = DB::table('order_items')
                    ->where('order_id', $orderId)
                    ->where('product_id', $item['product_id'])
                    ->first();
                
                if($existOrderItem){
                    // Update existing item - quantity
                    $newQty = $existOrderItem->quantity + $item['quantity'];
                    $newPrice = $newQty * $price;
                    
                    DB::table('order_items')
                        ->where('id', $existOrderItem->id)
                        ->update([
                            'quantity' => $newQty,
                            'price' => $newPrice,
                            'updated_at' => now()
                        ]);
                } else {
                    // Insert new item
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $price * $item['quantity'], 
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Deduct stock
                DB::table('products')
                    ->where('id', $product->id)
                    ->decrement('stock', $item['quantity']);
            }
            
            //  total price 
            $totalPrice = DB::table('order_items')
                ->where('order_id', $orderId)
                ->sum('price');  
            
            // Update order total
            DB::table('orders')->where('id', $orderId)->update([
                'total_price' => $totalPrice,
                'updated_at' => now(),
            ]);
        });
        
        return $this->sendResponse([], 'Order created/updated successfully');
        
    } catch(\Exception $e){
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
