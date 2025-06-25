<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalespersonOrderProduct;
use Auth;
use DB;

class PurchaseHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::with('salespersonCustomer')->where('user_id', Auth::user()->id)->orderBy('code', 'desc')->paginate(9);
        return view('frontend.user.purchase_history', compact('orders'));
    }    

    public function salesperson_customer_history()
    {
        $orders = Order::with('salespersonCustomerName')->where('salesperson_id', Auth::user()->id)->orderBy('code', 'desc')->paginate(9);
        return view('frontend.user.salesperson_customer_history', compact('orders'));

        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function salesperson_customer_index()
    {   

        $orders = SalespersonOrderProduct::/*with('customer','category','product')->*/where('salesperson_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(9);
        //echo "<pre>";print_r($orders);die;

        return view('frontend.user.salesperson_purchase_history', compact('orders'));
    }

    public function purchase_form()
    {
        $customerlist = User::select('name', 'id', 'email')->where('user_type', 'customer')->where('salesperson_id', Auth::user()->id)->where('banned', 0)->get();
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('frontend.user.purchase_form', compact('customerlist','categories'));    
    }

    public function custm_purchase_store(Request $request)
    {
        if (Auth::user()->is_salesperson == 1) {

            $unit_price = getProductUnitPrice($request->product_id);

            $salespersonOrderProduct = new SalespersonOrderProduct;
            $salespersonOrderProduct->customer_id= $request->customer_id;
            $salespersonOrderProduct->category_id= $request->category_id;
            $salespersonOrderProduct->product_id= $request->product_id;
            $salespersonOrderProduct->qty= $request->qty;
            $salespersonOrderProduct->description= $request->description;
            $salespersonOrderProduct->salesperson_id= Auth::user()->id;
            $salespersonOrderProduct->price= $unit_price;
            $salespersonOrderProduct->status= 'Pending';
            $salespersonOrderProduct->product_name= getProductName($request->product_id);
            $salespersonOrderProduct->customer_name= getUserName($request->customer_id);
            $salespersonOrderProduct->category_name= getCategoryName($request->category_id);
            if($salespersonOrderProduct->save()){
                flash(translate('Request has been send'))->success();
                return redirect()->route('dashboard');
            }else{
                flash(translate('Something went wrong! Please try again'))->error();
                return back();
            }
            
        }else{
            flash(translate('Your not authorized user'))->error();
            return back();
        }

    }

    public function get_all_product(Request $request)
    {
        $products = Product::with('brand')->where('category_id', $request->category_id)->where('published',1)->get();
        $html = '<option value="">'.translate("Select Product").'</option>';
        //echo "<pre>";print_r($products);die;
        foreach ($products as $product) {
            $html .= '<option value="' . $product->id . '" price="' . $product->unit_price . '">' . $product->name.' ('.$product->brand->name.')</option>';
        }
        
        echo json_encode($html);
    }

    public function digital_index()
    {
        $orders = DB::table('orders')
                        ->orderBy('code', 'desc')
                        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', '=', 'products.id')
                        ->where('orders.user_id', Auth::user()->id)
                        ->where('products.digital', '1')
                        ->where('order_details.payment_status', 'paid')
                        ->select('order_details.id')
                        ->paginate(15);
        return view('frontend.user.digital_purchase_history', compact('orders'));
    }

    public function purchase_history_details($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order->delivery_viewed = 1;
        $order->payment_status_viewed = 1;
        $order->save();
        return view('frontend.user.order_details_customer', compact('order'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function order_cancel($id)
    {
        $order = Order::where('id', $id)->where('user_id', auth()->user()->id)->first();
        if($order && ($order->delivery_status == 'pending' && $order->payment_status == 'unpaid')) {
            $order->delivery_status = 'cancelled';
            $order->save();

            flash(translate('Order has been canceled successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }

        return back();
    }

    /**
     * Show salesperson revenue
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function salesperson_monthly_revenue()
    {   

        $mainOrderArr = array();
        $customerlist = User::select('id')->where('salesperson_id', Auth::user()->id)->get()->toArray();
        $order = Order::select('grand_total','created_at','payment_status')->whereIn('user_id', $customerlist)/*->where('payment_status','paid')*/->get()->toArray();

        foreach ($order as $key => $value) { 

            $month_name = date('F', strtotime($value['created_at']));            
            if (isset($mainOrderArr[$month_name])) {
                $mainOrderArr[$month_name] += $value['grand_total'];
            }else{
                $mainOrderArr[$month_name] = $value['grand_total']; 
            }

        }

        return view('frontend.user.salesperson_monthly_revenue', compact('mainOrderArr'));        

    }
}
