<?php

namespace App\Utility;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductStock;

class CartUtility
{
    public static function addToCart($stockId, $userId = null, $tempUserId = null)
    {
        $stock = ProductStock::find($stockId);

        if (!$stock) {
            return ['success' => false, 'message' => 'Product stock not found.'];
        }

        $product = $stock->product;
        $quantity = $product->min_qty;

        if ($stock->qty < $quantity && !$product->digital) {
            return [
                'success' => false,
                'message' => "This product doesn't have enough stock for minimum purchase quantity {$product->min_qty}"
            ];
        }

        $cart = Cart::firstOrNew([
            'variation'    => $stock->variant,
            'user_id'      => $userId,
            'temp_user_id' => $tempUserId,
            'product_id'   => $product->id
        ]);

        if ($cart->exists) {
            if ($product->digital) {
                return ['success' => false, 'message' => 'This digital product is already in the cart.'];
            }

            $quantity = $cart->quantity + 1;
            if ($stock->qty < $quantity) {
                return ['success' => false, 'message' => 'This product does not have enough stock.'];
            }
        }

        $price = self::get_price($product, $stock, $quantity);
        $tax = self::tax_calculation($product, $price);

        self::save_cart_data($cart, $product, $price, $tax, $quantity);

        return ['success' => true, 'message' => 'Added to cart successfully'];
    }

    public static function get_price($product, $stock, $quantity)
    {
        $base_price = $stock->price;

        // Example discount logic (replace with real discount logic as needed)
        if ($product->discount_type === 'percent') {
            $base_price -= ($base_price * $product->discount) / 100;
        } elseif ($product->discount_type === 'amount') {
            $base_price -= $product->discount;
        }

        return $base_price * $quantity;
    }

    public static function tax_calculation($product, $price)
    {
        if ($product->tax_type === 'percent') {
            return ($price * $product->tax) / 100;
        } elseif ($product->tax_type === 'amount') {
            return $product->tax;
        }
        return 0;
    }

    public static function save_cart_data($cart, $product, $price, $tax, $quantity)
    {
        $cart->price = $price;
        $cart->tax = $tax;
        $cart->shipping_cost = 0;
        $cart->quantity = $quantity;
        $cart->save();
    }
}
