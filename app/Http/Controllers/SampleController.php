<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\OTPVerificationController;
use Illuminate\Http\Request;
use App\Http\Controllers\ClubPointController;
use App\Models\Order;
use App\Models\Cart;
use App\Models\SampleOrders;
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


class SampleController extends Controller
{
    /**
     * Display a listing of the resource to seller.
     *
     * @return \Illuminate\Http\Response
     */




    public function sample_orders(Request $request)
    {
        // Start building the query
        $query = SampleOrders::with(['product', 'user']) // Eager load product and user
                             ->orderBy('id', 'desc');
    
        // Apply filter if 'status' is present in the request
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
    
        // Paginate the results, preserving the filter in the URL
        $orders = $query->paginate(15);
    
        // Pass the orders to the view
        return view('backend.sales.sample_orders.index', compact('orders'));
    }
    
    public function updateOrderStatus(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'order_id' => 'required|integer|exists:sample_orders,id',  // Ensure order ID is valid
            'status' => 'required|string|in:pending,confirmed,cancelled',  // All possible statuses
        ]);
    
        // Find the order and update the status
        $order = SampleOrders::find($request->order_id);
        if ($order) {
            $order->status = $request->status;
            $order->save();
    
            // Respond with a success message
            return response()->json(['success' => true]);
        }
    
        // Respond with failure if the order is not found
        return response()->json(['success' => false, 'message' => 'Order not found']);
    }
    



public function sample_orders_show($id)
{
    try {
        // Load the order with the associated user
        $order = SampleOrders::with('user')->findOrFail($id);

        // Return the view with the order data
        return view('backend.sales.sample_orders.show', compact('order'));
    } catch (ModelNotFoundException $e) {
        \Log::error("Custom order with ID $id not found: " . $e->getMessage());
        return redirect()->route('sample_orders.index')->with('error', 'Order not found.');
    } catch (\Exception $e) {
        \Log::error("An error occurred while retrieving custom order ID $id: " . $e->getMessage());
        return redirect()->route('sample_orders.index')->with('error', 'An unexpected error occurred.');
    }
}



public function bulk_sample_order_delete(Request $request)
{
    if ($request->id) {
        // Soft delete the orders
        SampleOrders::whereIn('id', $request->id)->delete();
        return response()->json(['success' => true, 'message' => 'Orders soft deleted successfully.']);
    }
    return response()->json(['success' => false, 'message' => 'No orders selected.']);
}



  

    /**
     * Display a single sale to admin.
     *
     * @return \Illuminate\Http\Response
     */


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

}