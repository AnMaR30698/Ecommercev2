<?php

namespace App\Traits;

trait CartTrait
{
    public function addToCart($product)
    {

        $cart = auth()->user()->cart;
        $cart->products()->attach($product->id);

        return response()->json(['message' => 'تمت إضافة المنتج إلى السلة بنجاح']);
    }

    public function removeFromCart($product)
    {
        $cart = auth()->user()->cart;
        $cart->products()->detach($product->id);



        return response()->json(['message' => 'تمت إزالة المنتج  بنجاح']);
    }



}
