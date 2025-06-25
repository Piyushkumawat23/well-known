<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\OTPVerificationController;
use Illuminate\Http\Request;
use App\Http\Controllers\ClubPointController;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\CommissionHistory;
use App\Models\Color;
use App\Models\OrderDetail;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\OtpConfiguration;
use App\Models\User;
use App\Models\BusinessSetting;
use App\Models\CombinedOrder;
use App\Models\SmsTemplate;
use App\Models\SalespersonOrderProduct;
use App\Models\CustomOrder;
use Auth;
use Session;
use DB;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Utility\NotificationUtility;
use CoreComponentRepository;
use App\Utility\SmsUtility;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource to seller.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $orders = DB::table('orders')
            ->orderBy('id', 'desc')
            //->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('seller_id', Auth::user()->id)
            ->select('orders.id')
            ->distinct();

        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }

        $orders = $orders->paginate(15);

        foreach ($orders as $key => $value) {
            $order = \App\Models\Order::find($value->id);
            $order->viewed = 1;
            $order->save();
        }

        return view('seller.orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search'));
    }

    // All Orders
    public function all_orders(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $user_type = null;

        $orders = Order::with('salesperson','salespersonCustomer')->orderBy('id', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        if ($request->user_type != null) {
            if ($request->user_type == 'salesperson') {
                $orders = $orders->where('salesperson_id', '>', 0);
            }else{
                $orders = $orders->where('salesperson_id', 0); 
            }
            
            $user_type = $request->user_type;
        }

        $orders = $orders->paginate(15);
        return view('backend.sales.all_orders.index', compact('orders', 'sort_search', 'delivery_status', 'date', 'user_type'));
    }

        // All Orders
    public function custom_orders(Request $request)
    {
        /*CoreComponentRepository::instantiateShopRepository();*/


        $orders = CustomOrder::orderBy('id', 'desc');


        $orders = $orders->paginate(15);
        return view('backend.sales.custom_orders.index', compact('orders'));
    }

    public function custom_orders_show($id)
    {
        try {
            // Load the order with the associated user
            $order = CustomOrder::with('user')->findOrFail($id);

            return view('backend.sales.custom_orders.show', compact('order'));
        } catch (ModelNotFoundException $e) {
            \Log::error("Custom order with ID $id not found: " . $e->getMessage());
            return redirect()->route('custom_orders.index')->with('error', 'Order not found.');
        } catch (\Exception $e) {
            \Log::error("An error occurred while retrieving custom order ID $id: " . $e->getMessage());
            return redirect()->route('custom_orders.index')->with('error', 'An unexpected error occurred.');
        }
    }



    // All Orders
    public function salesperson_dashboard_orders(Request $request)
    {
        
        $date = $request->date;
        $sort_search = null;
        $status = null;

        $orders = SalespersonOrderProduct::with(/*'customer','category','product',*/'salesperson')->orderBy('id', 'desc');
        if ($request->status != null) {
            $orders = $orders->where('status', $request->status);
            $status = $request->status;
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }
        $orders = $orders->paginate(15);
        return view('backend.sales.all_salesperson_dashboard_orders.index', compact('orders', 'sort_search', 'status', 'date'));
    }

    public function salesperson_dashboard_orders_show($id)
    {
        //die('jhgv');
        $orderDetail = SalespersonOrderProduct::findOrFail($id);

        return view('backend.sales.all_salesperson_dashboard_orders.show', compact('orderDetail'));
    }
    public function all_orders_show($id)
    {
        $order = Order::with('salesperson','salespersonCustomer')->findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        return view('backend.sales.all_orders.show', compact('order', 'delivery_boys'));
    }

    // Inhouse Orders
    public function admin_orders(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $orders = Order::orderBy('id', 'desc')
                        ->where('seller_id', $admin_user_id);

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $orders = $orders->paginate(15);
        return view('backend.sales.inhouse_orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search', 'admin_user_id', 'date'));
    }

    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        $order->viewed = 1;
        $order->save();
        return view('backend.sales.inhouse_orders.show', compact('order', 'delivery_boys'));
    }

    // Seller Orders
    public function seller_orders(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $seller_id = $request->seller_id;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $orders = Order::orderBy('code', 'desc')
            ->where('orders.seller_id', '!=', $admin_user_id);

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }
        if ($seller_id) {
            $orders = $orders->where('seller_id', $seller_id);
        }

        $orders = $orders->paginate(15);
        return view('backend.sales.seller_orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search', 'admin_user_id', 'seller_id', 'date'));
    }

    public function seller_orders_show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order->viewed = 1;
        $order->save();
        return view('backend.sales.seller_orders.show', compact('order'));
    }


    // Pickup point orders
    public function pickup_point_order_index(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $orders = Order::query();
        if (Auth::user()->user_type == 'staff' && Auth::user()->staff->pick_up_point != null) {
            $orders->where('shipping_type', 'pickup_point')
                    ->where('pickup_point_id', Auth::user()->staff->pick_up_point->id)
                    ->orderBy('code', 'desc');

            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            }
            if ($date != null) {
                $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
            }

            $orders = $orders->paginate(15);

            return view('backend.sales.pickup_point_orders.index', compact('orders', 'sort_search', 'date'));
        } else {
            $orders->where('shipping_type', 'pickup_point')->orderBy('code', 'desc');

            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            }
            if ($date != null) {
                $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
            }

            $orders = $orders->paginate(15);

            return view('backend.sales.pickup_point_orders.index', compact('orders', 'sort_search', 'date'));
        }
    }

    public function pickup_point_order_sales_show($id)
    {
        if (Auth::user()->user_type == 'staff') {
            $order = Order::findOrFail(decrypt($id));
            $order_shipping_address = json_decode($order->shipping_address);
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();

            return view('backend.sales.pickup_point_orders.show', compact('order', 'delivery_boys'));
        } else {
            $order = Order::findOrFail(decrypt($id));
            $order_shipping_address = json_decode($order->shipping_address);
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();

            return view('backend.sales.pickup_point_orders.show', compact('order', 'delivery_boys'));
        }
    }

    /**
     * Display a single sale to admin.
     *
     * @return \Illuminate\Http\Response
     */

     public function search(Request $request)
     {
         $query = $request->q;
     
         $products = Product::where('name', 'like', '%' . $query . '%')
             ->where('published', 1) // only live products
             ->limit(10)
             ->get(['id', 'name', 'unit_price as price']);
     
         return response()->json($products);
     }

     
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create(Request $request)
    // {
    //     $user_id = $request->get('user_id'); // get from query string
    //     return view('backend.sales.all_orders.create_order', compact('user_id'));
    // }


    public function create(Request $request)
    {
        $user_id = $request->get('user_id');
    
        $user = \App\Models\User::find($user_id);
    
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
    
        // Prepare address info directly from users table
        $address_info = [
            'address'     => $user->address ?? '',
            'city'        => $user->city ?? '',
            'state'       => $user->state ?? '',
            'country'     => $user->country ?? '',
            'postal_code' => $user->postal_code ?? '',
        ];
        
        return view('backend.sales.all_orders.create_order', [
            'user_id'       => $user_id,
            'business_name' => $user->business_name ?? '',
            'address_info'  => $address_info,
            'addresses'     => $user->addresses, 
        ]);
    }
    


    public function adminstoreAddress(Request $request)
{
    $request->validate([
        'address' => 'required|string|max:255',
        'country_id' => 'required|exists:countries,id',
        'state_id' => 'required|exists:states,id',
        'city_id' => 'required|exists:cities,id',
        'postal_code' => 'required|string|max:20',
        'phone' => 'required|string|max:20',
        'latitude' => 'nullable|string',
        'longitude' => 'nullable|string',
        'user_id' => 'required|exists:users,id',
    ]);
 
    // Fetch user using user_id from the form
    $user_id = $request->input('user_id');
    $user = \App\Models\User::find($user_id);
 
    if (!$user) {
        return redirect()->back()->with('error', 'User not found.');
    }
 
    $address = new \App\Models\Address();
    $address->user_id = $user_id;
    $address->address = $request->input('address');
    $address->country_id = $request->input('country_id');
    $address->state_id = $request->input('state_id');
    $address->city_id = $request->input('city_id');
    $address->postal_code = $request->input('postal_code');
    $address->phone = $request->input('phone');
    $address->latitude = $request->input('latitude');
    $address->longitude = $request->input('longitude');
    $address->save();
 
    flash('Address saved successfully')->success();
    return back();
}


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();

       
            // echo '<pre>';print_r($carts);die;

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $address = Address::where('id', $carts[0]['address_id'])->first();

        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = Auth::user()->name;
            $shippingAddress['email']       = Auth::user()->email;
            $shippingAddress['address']     = $address->address;
            $shippingAddress['country']     = $address->country->name;
            $shippingAddress['state']       = $address->state->name;
            $shippingAddress['city']        = $address->city->name;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['phone']       = $address->phone;
            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = Auth::user()->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($carts as $cartItem){
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if(isset($seller_products[$product->user_id])){
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = Auth::user()->id;
            $order->shipping_address = $combined_order->shipping_address;
            $order->shipping_type = $carts[0]['shipping_type'];
            if ($carts[0]['shipping_type'] == 'pickup_point') {
                $order->pickup_point_id = $cartItem['pickup_point'];
            }
            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;

            //Order Details Storing
            foreach ($seller_product as $cartItem) {
                $product = Product::find($cartItem['product_id']);

                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $coupon_discount += $cartItem['discount'];

                $product_variation = $cartItem['variation'];

                $product_stock = $product->stocks->where('variant', $product_variation)->first();
                if(get_setting('out_stock_minimum_order') > 0 && $product_stock->qty < $cartItem['quantity']){
                    // if($request->quantity >= get_setting('out_stock_minimum_order')){
                    //     $cartItem['quantity'] = $request->quantity;
                    // }else{
                    //     $cartItem['quantity'] = get_setting('out_stock_minimum_order');   
                    // }
                }else{
                    if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                        flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                        $order->delete();
                        return redirect()->route('cart')->send();
                    } elseif ($product->digital != 1) {
                        $product_stock->qty -= $cartItem['quantity'];
                        $product_stock->save();
                    }
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $cartItem['shipping_cost'];

                $shipping += $order_detail->shipping_cost;
                //End of storing shipping cost

                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->save();

                $product->num_of_sale += $cartItem['quantity'];
                $product->save();

                $order->seller_id = $product->user_id;

                $order->salesperson_id = $cartItem['salesperson_id'];
                $order->for_customer_id = $cartItem['for_customer_id'];
                $order->shipping_type_payment = $cartItem['shipping_type_payment'];

                if ($product->added_by == 'seller' && $product->user->seller != null){
                    $seller = $product->user->seller;
                    $seller->num_of_sale += $cartItem['quantity'];
                    $seller->save();
                }

                if (addon_is_activated('affiliate_system')) {
                    if ($order_detail->product_referral_code) {
                        $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                    }
                }
            }
            $wholesaleCommissionAmount=wholesaleCommissionAmount($subtotal);
            $order->grand_total = ($subtotal + $tax + $shipping) - $wholesaleCommissionAmount;
            $order->wholesale_commission = $wholesaleCommissionAmount;

            $order->salesperson_id = Auth::user()->salesperson_id;

            if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $order->coupon_discount = $coupon_discount;
                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }

            $combined_order->grand_total += $order->grand_total;

            $order->save();
        }

        $combined_order->save();

        $request->session()->put('combined_order_id', $combined_order->id);
    }







    // public function adminstore(Request $request)
    // {
    //     $user_id = $request->input('user_id', Auth::id());
    //     $product_ids = $request->input('products', []);
    //     $quantities = $request->input('quantities', []);
    //     $payment_type = $request->input('payment_option', 'cod');
    //     $order_date = $request->input('order_date', now()->toDateString());
    
    //     // Validate input
    //     if (empty($product_ids) || empty($quantities) || count($product_ids) !== count($quantities)) {
    //         flash('Invalid product selection')->warning();
    //         return back();
    //     }
    
    //     $shippingAddress = [
    //         'name' => Auth::user()->name,
    //         'email' => Auth::user()->email,
    //         'address' => '',
    //         'country' => '',
    //         'state' => '',
    //         'city' => '',
    //         'postal_code' => '',
    //         'phone' => '',
    //         'lat_lang' => '',
    //     ];
    
    //     // Create Combined Order
    //     $combined_order = CombinedOrder::create([
    //         'user_id' => $user_id,
    //         'shipping_address' => json_encode($shippingAddress),
    //         'grand_total' => 0,
    //     ]);
    
    //     // Create Order
    //     $order = Order::create([
    //         'combined_order_id' => $combined_order->id,
    //         'user_id' => $user_id,
    //         'shipping_address' => json_encode($shippingAddress),
    //         'shipping_type' => 'home_delivery',
    //         'payment_type' => $payment_type,
    //         'delivery_viewed' => 0,
    //         'payment_status_viewed' => 0,
    //         'code' => now()->format('Ymd-His') . rand(10, 99),
    //         'date' => strtotime($order_date),
    //         'grand_total' => 0,
    //     ]);
    
    //     $grand_total = 0;
    
    //     foreach ($product_ids as $index => $product_id) {
    //         $quantity = max(1, (int)($quantities[$index] ?? 1));
    //         $product = Product::findOrFail($product_id);
    //         $price = $product->unit_price * $quantity;
    
    //         // Check stock
    //         if ($product->digital != 1) {
    //             $stock = $product->stocks()->first();
    //             if (!$stock || $stock->qty < $quantity) {
    //                 flash("Stock not available for {$product->name}")->warning();
    //                 return back();
    //             }
    //             $stock->decrement('qty', $quantity);
    //         }
    
    //         // Create Order Detail
    //         OrderDetail::create([
    //             'order_id' => $order->id,
    //             'seller_id' => $product->user_id,
    //             'product_id' => $product->id,
    //             'variation' => '',
    //             'price' => $price,
    //             'tax' => 0,
    //             'shipping_type' => 'home_delivery',
    //             'product_referral_code' => null,
    //             'shipping_cost' => 0,
    //             // 'gift_from' => null,
    //             // 'gift_recipient' => null,
    //             // 'gift_message' => null,
    //             'quantity' => $quantity,
    //         ]);
    
    //         // Update sale count
    //         $product->increment('num_of_sale', $quantity);
    //         $grand_total += $price;
    //     }
    
    //     // Update totals
    //     $order->update(['grand_total' => $grand_total]);
    //     $combined_order->update(['grand_total' => $grand_total]);
    
    //     flash('Order created successfully')->success();
    //     return redirect()->route('customers.index');
    // }
    


    public function adminstore(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'products' => 'required|array',
            'quantities' => 'required|array',
            'payment_option' => 'required|string',
            'order_date' => 'required|date',
            'address_id' => 'required|exists:addresses,id',
            'business_name' => 'nullable|string|max:255',
        ]);
    
        $user_id = $request->input('user_id', Auth::id());
        $product_ids = $request->products;
        $quantities = $request->quantities;
        $payment_type = $request->payment_option;
        $order_date = $request->order_date;
        $address_id = $request->address_id;
        $business_name = $request->input('business_name');
    
        // Validate product quantities
        if (count($product_ids) !== count($quantities)) {
            flash('Product quantities mismatch')->warning();
            return back();
        }
    
        $user = User::findOrFail($user_id);
        $address = Address::findOrFail($address_id);
    
        // Optional: Save business name to user profile if desired
        if ($business_name) {
            $user->business_name = $business_name;
            $user->save();
        }
    
        // Prepare shipping address
        $shippingAddress = [
            'name' => $user->name,
            'email' => $user->email,
            'address' => $address->address,
            'country' => optional($address->country)->name,
            'state' => optional($address->state)->name,
            'city' => optional($address->city)->name,
            'postal_code' => $address->postal_code,
            'phone' => $address->phone,
            'business_name' => $business_name ?? $user->business_name,
            'lat_lang' => '',
        ];
    
        // Create combined order
        $combined_order = CombinedOrder::create([
            'user_id' => $user_id,
            'shipping_address' => json_encode($shippingAddress),
            'grand_total' => 0,
        ]);
    
        // Create order
        $order = Order::create([
            'combined_order_id' => $combined_order->id,
            'user_id' => $user_id,
            'shipping_address' => json_encode($shippingAddress),
            'shipping_type' => 'home_delivery',
            'payment_type' => $payment_type,
            'delivery_viewed' => 0,
            'payment_status_viewed' => 0,
            'code' => now()->format('Ymd-His') . rand(10, 99),
            'date' => strtotime($order_date),
            'grand_total' => 0,
        ]);
    
        $grand_total = 0;
        $orderDetails = [];
    
        foreach ($product_ids as $i => $product_id) {
            $quantity = max(1, (int)($quantities[$i] ?? 1));
            $product = Product::findOrFail($product_id);
    
            // Check stock for physical products
            if (!$product->digital) {
                $stock = $product->stocks()->first();
                if (!$stock || $stock->qty < $quantity) {
                    flash("Not enough stock for {$product->name}")->warning();
                    return back();
                }
                $stock->decrement('qty', $quantity);
            }
    
            $price = $product->unit_price * $quantity;
            $grand_total += $price;
    
            $orderDetails[] = [
                'order_id' => $order->id,
                'seller_id' => $product->user_id,
                'product_id' => $product->id,
                'variation' => '',
                'price' => $price,
                'tax' => 0,
                'shipping_type' => 'home_delivery',
                'product_referral_code' => null,
                'shipping_cost' => 0,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $product->increment('num_of_sale', $quantity);
        }
    
        // Insert all order details in one query
        OrderDetail::insert($orderDetails);
    
        // Update order totals
        $order->update(['grand_total' => $grand_total]);
        $combined_order->update(['grand_total' => $grand_total]);
    
        flash('Order created successfully')->success();
        return redirect()->route('customers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order != null) {
            foreach ($order->orderDetails as $key => $orderDetail) {
                try {

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $orderDetail->variation)->first();
                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }

                } catch (\Exception $e) {

                }

                $orderDetail->delete();
            }
            $order->delete();
            flash(translate('Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function bulk_order_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->save();
        return view('seller.order_details_seller', compact('order'));
    }

    public function update_status(Request $request)
    {
        $order = SalespersonOrderProduct::findOrFail($request->order_id);
        $order->status = $request->status;
        $order->save();


        return 1;
    }
    public function update_delivery_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->balance += $order->grand_total;
            $user->save();
        }

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {

                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }

                if (addon_is_activated('affiliate_system')) {
                    if (($request->status == 'delivered' || $request->status == 'cancelled') &&
                        $orderDetail->product_referral_code) {

                        $no_of_delivered = 0;
                        $no_of_canceled = 0;

                        if ($request->status == 'delivered') {
                            $no_of_delivered = $orderDetail->quantity;
                        }
                        if ($request->status == 'cancelled') {
                            $no_of_canceled = $orderDetail->quantity;
                        }

                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                    }
                }
            }
        }
        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
            try {
                SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {

            }
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->delivery_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }

        return 1;
    }

   public function update_tracking_code(Request $request) {
        $order = Order::findOrFail($request->order_id);
        $order->tracking_code = $request->tracking_code;
        $order->save();

        return 1;
   }

    public function update_payment_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'payment_status_change')->first()->status == 1) {
            try {
                SmsUtility::payment_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {

            }
        }
        return 1;
    }

    public function assign_delivery_boy(Request $request)
    {
        if (addon_is_activated('delivery_boy')) {

            $order = Order::findOrFail($request->order_id);
            $order->assign_delivery_boy = $request->delivery_boy;
            $order->delivery_history_date = date("Y-m-d H:i:s");
            $order->save();

            $delivery_history = \App\Models\DeliveryHistory::where('order_id', $order->id)
                ->where('delivery_status', $order->delivery_status)
                ->first();

            if (empty($delivery_history)) {
                $delivery_history = new \App\Models\DeliveryHistory;

                $delivery_history->order_id = $order->id;
                $delivery_history->delivery_status = $order->delivery_status;
                $delivery_history->payment_type = $order->payment_type;
            }
            $delivery_history->delivery_boy_id = $request->delivery_boy;

            $delivery_history->save();

            if (env('MAIL_USERNAME') != null && get_setting('delivery_boy_mail_notification') == '1') {
                $array['view'] = 'emails.invoice';
                $array['subject'] = translate('You are assigned to delivery an order. Order code') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;

                try {
                    Mail::to($order->delivery_boy->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {

                }
            }

            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'assign_delivery_boy')->first()->status == 1) {
                try {
                    SmsUtility::assign_delivery_boy($order->delivery_boy->phone, $order->code);
                } catch (\Exception $e) {

                }
            }
        }

        return 1;
    }
    public function order_cancel($id)
    {
        $order = Order::where('id', $id)->first(); 
        $user_info=json_decode($order->shipping_address,true);
        // echo'<pre>'; print_r($user_info);
        // echo'<pre> order:-'; print_r($order);
        // echo $user_info['email'];
        // die;
            
        if($order ) {
            $order->delivery_status = 'cancelled';
            
            if ($order->save()) {
                    $array['view'] = 'emails.invoice';
                    $array['subject'] = translate('Order cancelled ')."-".$order->code ;
                    $array['from'] =  env('MAIL_FROM_ADDRESS');
                    $array['order'] = $order;


                    // echo'<pre>'; print_r($array);die;
                    try {
                        Mail::to(get_setting('admin_email'))->queue(new InvoiceEmailManager($array));
                        Mail::to($user_info['email'])->queue(new InvoiceEmailManager($array));
                    } catch (\Exception $e) {
    
                    }
                }
            flash(translate('Order has been cancelled successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }

        return redirect()->route('all_orders.index');
    }
}
