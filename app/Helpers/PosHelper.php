<?php

use App\Models\Cart;
use Illuminate\Support\Facades\Session;

if (!function_exists('cart_product_price')) {
    function cart_product_price($cartItem, $product, $with_discount = true, $with_tax = true)
    {
        $price = $cartItem['price'] ?? 0;

        if ($with_discount && isset($product->discount)) {
            $price -= $product->discount;
        }

        if ($with_tax && isset($product->tax)) {
            $price += $product->tax;
        }

        return $price;
    }
}

if (!function_exists('get_pos_user_cart')) {
    function get_pos_user_cart()
    {
        $userID = Session::get('pos.user_id');
        $tempUserID = Session::get('pos.temp_user_id');

        if ($userID) {
            return Cart::where('user_id', $userID)->latest()->get();
        } elseif ($tempUserID) {
            return Cart::where('temp_user_id', $tempUserID)->latest()->get();
        } else {
            return collect();
        }
    }
}


if (!function_exists('cart_product_tax')) {
    function cart_product_tax($cartItem, $product, $with_discount = true)
    {
        $tax = 0;

        if (isset($product->tax)) {
            $price = $cartItem['price'] ?? 0;

            if ($with_discount && isset($product->discount)) {
                $price -= $product->discount;
            }

            // Assuming $product->tax is a fixed amount. If it's a percentage, adjust accordingly.
            $tax = $product->tax; // or: $tax = ($price * $product->tax) / 100;
        }

        return $tax;
    }
}
