<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function getTotalPrice()
    {
        $totalPrice = 0;
        foreach ($this->cartItems as $cartItem) {
            $productPrice = $cartItem->product->price;
            $itemQuantity = $cartItem->quantity;
            $itemPrice = $productPrice * $itemQuantity;
            $totalPrice += $itemPrice;
        }
        return $totalPrice;
    }
    public function makeOrder()
    {
        $userId = $this->user_id;
        $totalPrice = $this->getTotalPrice();
        if($totalPrice == 0){
            return false;
        }

        $order = new Order([
            'customer_id' => $userId,
            'total_price' => $totalPrice,
            'state' => 'ordered',
        ]);
        $order->save();

        // Create order items for each cart item
        foreach ($this->cartItems as $cartItem) {
            $orderItem = new OrderItem([
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
                'order_id' => $order->id,
            ]);
            $orderItem->save();
        }

        // Delete the cart items
        // $this->cartItems()->delete();

        // Return the order object
        return $order;
    }
}

