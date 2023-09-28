<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function getCartItems()
    {
        $user = Auth::user();

        if($user->hasAnyRole(['customer'])){
            $cart = $user->cart;
            if(!$cart){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart not found you do not have account'
                ],Response::HTTP_NOT_FOUND);
            }
            $cartItems = $cart->cartItems()->with('product')->get();
            if(!$cartItems){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart is empty'
                ],Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'status'=>'success',
                'message'=>'these is your cart items',
                'data' => $cartItems
            ]);
        }else{
            return response()->json([
                'status'=>'error',
                'message' => 'you are not customer you do not have cart'
            ],Response::HTTP_UNAUTHORIZED);
        }
    }
    public function addItemToCart(Request $request)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['customer'])){
            $cart = $user->cart;
            if(!$cart){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart not found you do not have account'
                ],Response::HTTP_NOT_FOUND);
            }
            $product = Product::find($request->product_id);
            if(!$product){
                return response()->json([
                    'status' => 'error',
                    'message' => 'product not found'
                ],Response::HTTP_NOT_FOUND);
            }
            $cartItem = new CartItem([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price'=>$product->price * $request->quantity
            ]);
            $cart->cartItems()->save($cartItem);
            $this->getTotalPrice();
            return response()->json([
                'status'=>'success',
                'message'=>'cartItem added successfully',
                'data' => $cartItem,
            ], Response::HTTP_CREATED);
        }
    }
    public function updateCartItemQuantity(Request $request,$cart_item_id)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['customer'])){

            $cartItem = Cart::where('user_id', $user->id)
                            ->firstOrFail()
                            ->cartItems()
                            ->find($cart_item_id);
            if(!$cartItem){
                return response()->json([
                    'status' => 'error',
                    'message' => 'cartItem not found'
                ],Response::HTTP_NOT_FOUND);
            }
            $product = Product::find($cartItem->product_id);
            if(!$product){
                return response()->json([
                    'status' => 'error',
                    'message' => 'product not found'
                ],Response::HTTP_NOT_FOUND);
            }
            $cartItem->update([
                'quantity' => $request->quantity,
                'price'=>$product->price * $request->quantity,
            ]);

            $totalPrice = $this->getTotalPrice();
            return response()->json([
                'status'=>'success',
                'message'=>'cartItem updated successfully',
                'data' => $cartItem
            ],Response::HTTP_ACCEPTED);
        }
    }
    public function removeCartItem($cart_item_id)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['customer'])){
            $cartItem = Cart::where('user_id', $user->id)
                            ->firstOrFail()
                            ->cartItems()
                            ->find($cart_item_id);
            if(!$cartItem){
                return response()->json([
                    "statue"=>"error",
                    "message"=>"cart item not found"
                ], Response::HTTP_NOT_FOUND);
            }
            $cartItem->delete();
            return response()->json([
                "statue"=>"success",
                "message"=>"cartItem deleted successfuly"
            ], Response::HTTP_ACCEPTED);
        }
    }
    public function getTotalPrice()
    {
        $userId = Auth::id();

        $cart = Cart::where('user_id', $userId)->first();
        if(!$cart){
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found you do not have account'
            ],Response::HTTP_NOT_FOUND);
        }

        $totalPrice = $cart->getTotalPrice();

        return response()->json([
            'total_price' => $totalPrice,
        ]);
    }
    public function makeOrder()
    {
        $userId = Auth::id();

        $cart = Cart::where('user_id', $userId)->first();
    
        $order = $cart->makeOrder();
        if (!$order) {
            return response()->json([
                'status'=>'error',
                'message'=>'there is nothing to order'
            ]);
        }
    
        // Get the order items and related product and store information
        $orderItems = [];
        foreach ($order->orderItems as $orderItem) {
            $product = Product::with('store')->find($orderItem->product_id);
            $store = $product->store;
    
            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'store_id' => $store->id,
                'store_name' => $store->store_name,
                'store_address' => $store->store_address,
                'quantity' => $orderItem->quantity,
                'price' => $orderItem->price,
            ];
        }
    
        return response()->json([
            'order_id' => $order->id,
            'total_price' => $order->total_price,
            'state' => $order->state,
            'order_details' => $orderItems,
        ]);
    }
}
