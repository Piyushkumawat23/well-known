<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Http\Resources\PosProductCollection;
use App\Models\Cart;
use App\Models\Product;
use App\Utility\FontUtility;
use App\Utility\PosUtility;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Artisan;
use Session;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Validator;


class PosController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:pos_manager'])->only('admin_index');
        $this->middleware(['permission:pos_configuration'])->only('pos_activation');
    }

    // public function index()
    // {
    //     $customers = User::where('user_type', 'customer')->where('email_verified_at', '!=', null)->orderBy('created_at', 'desc')->get();
    //     return view('backend.pos.index', compact('customers'));
    // }


    public function index()
    {
        $customers = User::where('user_type', 'customer')->where('email_verified_at', '!=', null)->orderBy('created_at', 'desc')->get();
    
        $userID = Session::get('pos.user_id');
        $tempUserID = Session::get('pos.temp_user_id');
    
        if ($userID) {
            $carts = Cart::where('user_id', $userID)->latest()->get();
        } elseif ($tempUserID) {
            $carts = Cart::where('temp_user_id', $tempUserID)->latest()->get();
        } else {
            $carts = collect();
        }
    
        return view('backend.pos.index', compact('customers', 'carts'));
    }
    

    // public function search(Request $request)
    // {
    //     $products = PosUtility::product_search($request->only('category', 'brand', 'keyword'));

    //     $stocks = new PosProductCollection($products);
    //     $stocks->appends(['keyword' =>  $request->keyword, 'category' => $request->category, 'brand' => $request->brand]);
    //     return $stocks;
    // }


    
    // public function search(Request $request)
    // {
    //     $products = PosUtility::product_search($request->only('category', 'brand', 'keyword'));
    
    //     if ($products->isEmpty()) {
    //         return response()->json([], 200); // safe empty response
    //     }
    
    //     $stocks = new PosProductCollection($products);
    //     return $stocks;
    // }
    

    public function search(Request $request)
    {
        try {
            $keyword = strtolower($request->input('keyword'));
            $category = $request->input('category');
            $brand = $request->input('brand');
    
            $products = PosUtility::product_search([
                'category' => $category,
                'brand' => $brand,
                'keyword' => $keyword,
            ]);
    
            if ($products->isEmpty() && $keyword) {
                $products = Product::whereHas('stocks', function ($query) use ($keyword) {
                    $query->where('sku', 'LIKE', '%' . $keyword . '%');
                })
                ->with(['stocks' => function ($q) use ($keyword) {
                    $q->where('sku', 'LIKE', '%' . $keyword . '%');
                }])
                ->get();
            
    
                if ($products->isEmpty()) {
                    return response()->json([], 200);
                }
            }
    
            if (!$products->isEmpty()) {
                Artisan::call('cache:clear');
            }
            
            $stocks = new PosProductCollection($products);
            return $stocks;
        } catch (\Exception $e) {
            // Log the error message to storage/logs/laravel.log or wherever you want
            \Log::error('Product search error: ' . $e->getMessage());
    
            // Return a safe JSON response instead of error page
            return response()->json(['error' => 'Product search failed.'], 500);
        }
    }

    public function autosearch(Request $request)
{
    try {
        $keyword = strtolower(trim($request->input('keyword')));
        $category = $request->input('category');
        $brand = $request->input('brand');

        if (empty($keyword)) {
            return response()->json([], 200);
        }

        // Exact SKU match only
        $products = Product::whereHas('stocks', function ($query) use ($keyword) {
                $query->whereRaw('LOWER(sku) = ?', [$keyword]);
            })
            ->with(['stocks' => function ($q) use ($keyword) {
                $q->whereRaw('LOWER(sku) = ?', [$keyword])
                  ->select('id', 'product_id', 'sku', 'qty', 'price');
            }]);

        // Apply optional filters
        if (!empty($category)) {
            $products->where('category_id', $category);
        }

        if (!empty($brand)) {
            $products->where('brand_id', $brand);
        }

        $products = $products->get();

        if ($products->isEmpty()) {
            return response()->json([], 200);
        }


        Artisan::call('cache:clear');

        return response()->json([
            'success' => true,
            'data' => PosProductCollection::make($products)->resolve()
        ]);

    } catch (\Exception $e) {
        \Log::error('AutoSearch Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Product search failed.'
        ], 500);
    }
}


    // Add product To cart
    // public function addToCart(Request $request)
    // {   
    //     $stockId    = $request->stock_id;
    //     $userID     = Session::get('pos.user_id');
    //     $temUserId  = Session::get('pos.temp_user_id');
        
    //     if (!$temUserId && !$userID) {
    //         $temUserId = bin2hex(random_bytes(10));
    //         Session::put('pos.temp_user_id', $temUserId);
    //     }
    //     $response = PosUtility::addToCart($stockId, $userID, $temUserId);
        
    //     return array(
    //         'success' => $response['success'],
    //         'message' => $response['message'],
    //         'view' => view('backend.pos.cart')->render()
    //     );
    // }




    public function addToCart(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|integer|exists:product_stocks,id'
        ]);
    
        $stockId = $request->input('stock_id');
        $userID = Session::get('pos.user_id');
        $tempUserId = Session::get('pos.temp_user_id');
    
        if (!$tempUserId && !$userID) {
            try {
                $tempUserId = bin2hex(random_bytes(10));
                Session::put('pos.temp_user_id', $tempUserId);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate temporary user ID.'
                ], 500);
            }
        }
    
        $response = PosUtility::addToCart($stockId, $userID, $tempUserId);
    
        if (!isset($response['success']) || !isset($response['message'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error while adding to cart.'
            ], 500);
        }
    
        return response()->json([
            'success' => $response['success'],
            'message' => $response['message'],
            'view' => view('backend.pos.cart')->render()
        ]);
    }
    



    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $cart = Cart::find($request->cartId);
        $response = PosUtility::updateCartItemQuantity($cart, $request->only(['cartId', 'quantity']));

        return array('success' => $response['success'], 'message' => $response['message'], 'view' => view('backend.pos.cart')->render());
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        Cart::where('id', $request->id)->delete();
        return view('backend.pos.cart');
    }

    //Shipping Address for admin
    public function getShippingAddress(Request $request)
    {
        Session::forget('pos.shipping_info');
        $user_id = $request->id;
        return ($user_id == '') ? view('backend.pos.guest_shipping_address') : view('backend.pos.shipping_address', compact('user_id'));
    }

    public function set_shipping_address(Request $request)
    {
        $data = PosUtility::get_shipping_address($request);

        $shipping_info = $data;
        $request->session()->put('pos.shipping_info', $shipping_info);
    }

    // Update user Cart data when user is changed 
    public function updateSessionUserCartData(Request $request)
    {
        PosUtility::updateCartOnUserChange($request->only(['userId']));
        return view('backend.pos.cart');
    }

    //set Discount
    public function setDiscount(Request $request)
    {
        if ($request->discount >= 0) {
            Session::put('pos.discount', $request->discount);
        }
        return view('backend.pos.cart');
    }

    //set Shipping Cost
    public function setShipping(Request $request)
    {
        if ($request->shipping != null) {
            Session::put('pos.shipping', $request->shipping);
        }
        return view('backend.pos.cart');
    }

    //order summary
    public function get_order_summary(Request $request)
    {
        return view('backend.pos.order_summary');
    }

    //order place
    // public function order_store(Request $request)
    // {
    //     $request->merge(['temp_usder_id' => Session::get('pos.temp_user_id'),
    //      'shippingInfo' => Session::get('pos.shipping_info'), 
    //      'shippingCost' => Session::get('pos.shipping', 0), 'discount' => Session::get('pos.discount')]);
    //     $response = PosUtility::orderStore($request->except(['_token']));

    //     if ($response['success']) {
    //         Session::forget('pos.shipping_info');
    //         Session::forget('pos.shipping');
    //         Session::forget('pos.discount');
    //         Session::forget('pos.user_id');
    //         Session::forget('pos.temp_user_id');
    //     }

    //     return $response;
    // }

    public function order_store(Request $request)
{
    try {
        // Use request data, fallback to session
        $tempUserId = $request->input('user_id') ?? Session::get('pos.temp_user_id');
        $shippingInfo = Session::get('pos.shipping_info', []);
        $shippingCost = $request->input('shipping') ?? Session::get('pos.shipping', 0);
        $discount = $request->input('discount') ?? Session::get('pos.discount', 0);

        if (!$tempUserId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is missing.',
            ], 400);
        }

        // Prepare and sanitize data for order
        $orderData = [
            'user_id'           => (int) $tempUserId,
            'shippingInfo'           => $shippingInfo,
            'shippingCost'           => (float) $shippingCost,
            'discount'               => (float) $discount,
            'payment_type'           => $request->input('payment_type'),
            'shipping_address'       => $request->input('shipping_address'),
            'offline_payment_method' => $request->input('offline_payment_method'),
            'offline_payment_amount' => $request->input('offline_payment_amount'),
            'offline_trx_id'         => $request->input('offline_trx_id'),
            'offline_payment_proof'  => $request->input('offline_payment_proof'),
        ];

        // Validate required fields
        $validator = Validator::make($orderData, [
            'user_id'     => 'required|integer',
            'payment_type'     => 'required|string',
            'shipping_address' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Log data for debug (optional)
        \Log::info('Order submission', $orderData);

        // Call the utility
        $response = PosUtility::orderStore($orderData);

        if (!empty($response['success'])) {
            Session::forget([
                'pos.shipping_info',
                'pos.shipping',
                'pos.discount',
                'pos.user_id',
                'pos.temp_user_id',
            ]);
        }

        return response()->json($response);

    } catch (\Throwable $e) {
        \Log::error('POS Order Store Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An internal server error occurred.',
        ], 500);
    }
}

        

    public function configuration()
    {
        return view('backend.pos.pos_activation');
    }

    public function invoice($id)
    {
        $order = Order::findOrFail($id);

        $print_width = get_setting('print_width');
        if ($print_width == null) {
            flash(translate('Thermal printer size is not given in POS configuration'))->warning();
            return back();
        }

        $pdf_style_data = FontUtility::get_font_family();

        $html = view('backend.pos.thermal_invoice', compact('order', 'pdf_style_data'));

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => [$print_width, 1000]]);
        $mpdf->WriteHTML($html);
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        $mpdf->page   = 0;
        $mpdf->state  = 0;
        unset($mpdf->pages[0]);
        // The $p needs to be passed by reference
        $p = 'P';
        // dd($mpdf->y);
        $mpdf->_setPageSize(array($print_width, $mpdf->y), $p);

        $mpdf->addPage();
        $mpdf->WriteHTML($html);

        $mpdf->Output('order-' . $order->code . '.pdf', 'I');
    }
}
