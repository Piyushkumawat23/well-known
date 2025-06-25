<?php

namespace App\Utility;

use App\Models\ProductStock;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Session;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Models\User;

class PosUtility
{
    public static function product_search($request_data): object
    {
        $product_query = ProductStock::query()->join('products', 'product_stocks.product_id', '=', 'products.id');

        if (auth()->user()->user_type == 'seller') {
            $product_query->where('products.user_id', auth()->user()->id);
        } else {
            $product_query->where('products.added_by', 'admin');
        }
        $products = $product_query->where('products.auction_product', 0)
            ->where('products.wholesale_product', 0)
            ->where('products.published', 1)
            ->where('products.approved', 1)
            ->select('products.*', 'product_stocks.id as stock_id', 'product_stocks.variant', 'product_stocks.price as stock_price', 'product_stocks.qty as stock_qty', 'product_stocks.image as stock_image')
            ->orderBy('products.created_at', 'desc');


        if ($request_data['category'] != null) {
            $arr = explode('-', $request_data['category']);
            if ($arr[0] == 'category') {
                $category_ids = CategoryUtility::children_ids($arr[1]);
                $category_ids[] = $arr[1];
                $products = $products->whereIn('products.category_id', $category_ids);
            }
        }

        if ($request_data['brand'] != null) {
            $products = $products->where('products.brand_id', $request_data['brand']);
        }

        // if ($request_data['keyword'] != null) {
        //     $products = $products->where('products.name', 'like', '%' . $request_data['keyword'] . '%')->orWhere('products.barcode', $request_data['keyword']);
        // }

        if ($request_data['keyword'] != null) {
            $products = $products->where(function ($q) use ($request_data) {
                $q->where('products.name', 'like', '%' . $request_data['keyword'] . '%')
                  ->orWhere('products.barcode', $request_data['keyword'])
                  ->orWhere('product_stocks.sku', 'like', '%' . $request_data['keyword'] . '%');
            });
        }
        
        return $products->paginate(16);
    }

    public static function get_shipping_address($request): array
    {
        if ($request->address_id != null) {
            $address = Address::findOrFail($request->address_id);
            $data['name'] = $address->user->name;
            $data['email'] = $address->user->email;
            $data['address'] = $address->address;
            $data['country'] = $address->country->name;
            $data['state'] = $address->state->name;
            $data['city'] = $address->city->name;
            $data['postal_code'] = $address->postal_code;
            $data['phone'] = $address->phone;
        } else {
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['address'] = $request->address;
            $data['country'] = Country::find($request->country_id)->name;
            $data['state'] = State::find($request->state_id)->name;
            $data['city'] = City::find($request->city_id)->name;
            $data['postal_code'] = $request->postal_code;
            $data['phone'] = $request->phone;
        }

        return $data;
    }

    public static function addToCart($stockId, $userID, $temUserId)
    {
        $productStock   = ProductStock::find($stockId);
        $product        = $productStock->product;
        $quantity       = $product->min_qty;

        if ($productStock->qty < $product->min_qty && $product->digital == 0) {
            return array(
                'success' => 0,
                'message' => translate("This product doesn't have enough stock for minimum purchase quantity ") . $product->min_qty
            );
        }

        $cart = Cart::firstOrNew([
            'variation' => $productStock->variant,
            'user_id' => $userID,
            'temp_user_id' => $temUserId,
            'product_id' => $product->id
        ]);

        if ($cart->exists) {
            if ($product->digital == 1) {
                return array(
                    'success' => 0,
                    'message' => translate("This product is alreday in the cart")
                );
            } else {
                $quantity = $cart->quantity + 1;
                if ($productStock->qty < $quantity) {
                    return array(
                        'success' => 0,
                        'message' => translate("This product doesn't have more stock.")
                    );
                }
            }
        }

        $price = CartUtility::get_price($product, $productStock, $quantity);
        $tax = CartUtility::tax_calculation($product, $price);
        CartUtility::save_cart_data($cart, $product, $price, $tax, $quantity);
        return array('success' => 1, 'message' => 'Added to cart successfully');
    }

    public static function updateCartItemQuantity($cart, $data)
    {
        $product = Product::find($cart->product_id);
        $product_stock = $product->stocks->where('variant', $cart->variation)->first();

        if ($product_stock->qty < $data['quantity']) {
            $response['success'] = 0;
            $response['message'] = translate("This product doesn't have more stock.");
        } else {
            $cart->quantity = $data['quantity'];
            $cart->save();
            $response['success'] = 1;
            $response['message'] = translate("Updated the item successfully.");
        }

        return $response;
    }

    public static function updateCartOnUserChange($data)
    {
        $userID             = $data['userId'];
        $sessionUserId      = Session::has('pos.user_id') ? Session::get('pos.user_id') : null;
        $sessionTemUserId   = Session::has('pos.temp_user_id') ? Session::get('pos.temp_user_id') : null;
        $carts              = get_pos_user_cart();

        // If user is selected but user not in session or Session user is not this user, set it to session
        if ($userID) {
            if ($carts) {
                self::updatePosUserCartData($carts, $userID, null);
            }

            if (!$sessionUserId || ($sessionUserId != $userID)) {
                Session::put('pos.user_id', $userID);
            }
            Session::forget('pos.temp_user_id');
        }

        // If user is not selected, and if session has not Temp user ID, get it or set it
        if (!$userID) {
            if (!$sessionTemUserId) {
                $sessionTemUserId = bin2hex(random_bytes(10));
                Session::put('pos.temp_user_id', $sessionTemUserId);
            }
            if ($carts) {
                self::updatePosUserCartData($carts, null, $sessionTemUserId);
            }
            Session::forget('pos.user_id');
        }
    }

    public static function updatePosUserCartData($carts, $userID, $tempUsderID)
    {
        foreach ($carts as $cartItem) {
            $userCartItem = Cart::where('user_id', $userID)->where('temp_user_id', $tempUsderID)->where('product_id', $cartItem->product_id)->where('variation', $cartItem->variation)->first();
            if ($userCartItem) {
                $quantity = $userCartItem->quantity + $cartItem->quantity;
                $product_qty = $cartItem->product->stocks()->where('variant', $cartItem->$cartItem)->first();
                $quantity = $product_qty > $quantity ? $product_qty : $quantity;

                $userCartItem->update(['quantity' => $quantity]);
                $cartItem->delete();
            } else {
                $cartItem->update(['user_id' => $userID, 'temp_user_id' => $tempUsderID]);
            }
        }
    }

    public static function orderStore($data)
{
    $shippingInfo = $data['shippingInfo'] ?? null;

    if (
        !$shippingInfo ||
        empty($shippingInfo['name']) ||
        empty($shippingInfo['phone']) ||
        empty($shippingInfo['address'])
    ) {
        return ['success' => 0, 'message' => translate("Please Add Shipping Information.")];
    }

    $userId = $data['user_id'] ?? null;
    $tempUserId = $data['temp_user_id'] ?? null;
    $carts = get_pos_user_cart($userId, $tempUserId);

    if (count($carts) === 0) {
        return ['success' => 0, 'message' => translate("Please select a product.")];
    }

    $order = new Order();
    if ($userId === null) {
        $order->guest_id = $carts[0]->temp_user_id;
    } else {
        $order->user_id = $userId;
    }

    $order->shipping_address = json_encode($shippingInfo);
    $order->payment_type = $data['payment_type'] ?? 'cash_on_delivery';
    $order->delivery_viewed = 0;
    $order->payment_status_viewed = 0;
    $order->code = date('Ymd-His') . rand(10, 99);
    $order->date = time();
    $order->payment_status = ($order->payment_type != 'cash_on_delivery') ? 'paid' : 'unpaid';
    $order->payment_details = $order->payment_type;
    $order->payment_type  = 'pos';

    // Handle offline payment
    if ($order->payment_type === 'offline_payment') {
        if (empty($data['offline_trx_id'])) {
            return ['success' => 0, 'message' => translate("Transaction ID cannot be null.")];
        }

        $manualPayment = [
            'name' => $data['offline_payment_method'] ?? '',
            'amount' => $data['offline_payment_amount'] ?? 0,
            'trx_id' => $data['offline_trx_id'],
            'photo' => $data['offline_payment_proof'] ?? '',
        ];

        $order->manual_payment_data = json_encode($manualPayment);
        $order->manual_payment = 1;
    }

    if (!$order->save()) {
        return ['success' => 0, 'message' => translate('Could not save the order.')];
    }

    $subtotal = 0;
    $tax = 0;
    $shippingCost = $data['shippingCost'] ?? 0;
    $discount = $data['discount'] ?? 0;

    foreach ($carts as $cartItem) {
        $product_stock = $cartItem->product->stocks->where('variant', $cartItem->variation)->first();
        $product = $product_stock->product;
        $product_variation = $product_stock->variant;

        $subtotal += $cartItem->price * $cartItem->quantity;
        $tax += $cartItem->tax * $cartItem->quantity;

        if (!$product->digital && $cartItem->quantity > $product_stock->qty) {
            $order->delete();
            return ['success' => 0, 'message' => "{$product->name} ({$product_variation}) " . translate(" just stock outs.")];
        }

        if (!$product->digital) {
            $product_stock->qty -= $cartItem->quantity;
            $product_stock->save();
        }

        $order_detail = new OrderDetail();
        $order_detail->order_id = $order->id;
        $order_detail->seller_id = $product->user_id;
        $order_detail->product_id = $product->id;
        $order_detail->payment_status = $order->payment_status;
        $order_detail->variation = $product_variation;
        $order_detail->price = $cartItem->price * $cartItem->quantity;
        $order_detail->tax = $cartItem->tax * $cartItem->quantity;
        $order_detail->quantity = $cartItem->quantity;
        $order_detail->shipping_type = null;
        $order_detail->shipping_cost = $shippingCost > 0 ? ($shippingCost / count($carts)) : 0;
        $order_detail->save();

        $product->num_of_sale++;
        $product->save();
    }

    // Finalize order total
    $order->grand_total = $subtotal + $tax + $shippingCost;
    if ($discount > 0) {
        $order->grand_total -= $discount;
        $order->coupon_discount = $discount;
    }
    $order->seller_id = $product->user_id ?? null;
    $order->save();

    // Send Emails
    $array = [
        'view' => 'emails.invoice',
        'subject' => 'Your order has been placed - ' . $order->code,
        'from' => env('MAIL_USERNAME'),
        'order' => $order
    ];

    $admin_products = [];
    $seller_products = [];

    foreach ($order->orderDetails as $detail) {
        $product = $detail->product;
        if ($product->added_by === 'admin') {
            $admin_products[] = $product->id;
        } else {
            $seller_products[$product->user_id][] = $product->id;
        }
    }

    foreach ($seller_products as $sellerId => $products) {
        try {
            Mail::to(User::find($sellerId)->email)->queue(new InvoiceEmailManager($array));
        } catch (\Exception $e) {
            // Log error if needed
        }
    }

    if (env('MAIL_USERNAME')) {
        try {
            Mail::to($shippingInfo['email'])->queue(new InvoiceEmailManager($array));
            Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
        } catch (\Exception $e) {
            // Log error if needed
        }
    }

    if ($userId && $order->payment_status === 'paid') {
        calculateCommissionAffilationClubPoint($order);
    }

    // Clear cart
    Cart::where('user_id', $order->user_id)->orWhere('temp_user_id', $order->guest_id)->delete();

    return ['success' => 1, 'message' => translate('Order Completed Successfully.')];
}

    
}
