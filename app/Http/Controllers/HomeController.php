<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Brand;
use App\Models\Product;
use App\Models\PickupPoint;
use App\Models\CustomerPackage;
use App\Models\User;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\Order;
use App\Models\BusinessSetting;
use App\Models\Coupon;
use App\Models\RelatedProduct;
use App\Models\ProductVariantImage;
use App\Models\CustomOrder;
use App\Models\SampleOrders;
use Cookie;
use Illuminate\Support\Str;
use App\Mail\SecondEmailVerifyMailManager;
use App\Models\AffiliateConfig;
use App\Models\Page;
use Mail;
use Illuminate\Auth\Events\PasswordReset;
use Cache;


class HomeController extends Controller
{
    /**
     * Show the application frontend home.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $featured_categories = Cache::rememberForever('featured_categories', function () {
            return Category::where('featured', 1)->get();
        });

        $todays_deal_products = Cache::rememberForever('todays_deal_products', function () {
            return filter_products(Product::where('published', 1)->where('todays_deal', '1'))->get();            
        });

        /*$newest_products = Cache::remember('newest_products', 3600, function () {
            return filter_products(Product::where('is_group_main_product',1)->latest())->limit(12)->get();
        });*/
        $newest_products = Product::where('is_new_arrival',1)->where('published', 1)->limit(12)->get();

        return view('frontend.index', compact('featured_categories', 'todays_deal_products', 'newest_products'));
    }

    public function login()
    {
        if(Auth::check()){
            return redirect()->route('home');
        }
        return view('frontend.user_login');
    }

    public function registration(Request $request)
    {
        if(Auth::check()){
            return redirect()->route('home');
        }
        if($request->has('referral_code') && addon_is_activated('affiliate_system')) {
            try {
                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }

                Cookie::queue('referral_code', $request->referral_code, $cookie_minute);
                $referred_by_user = User::where('referral_code', $request->referral_code)->first();

                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            } catch (\Exception $e) {

            }
        }
        return view('frontend.user_registration');
    }

    public function cart_login(Request $request)
    {
        $user = null;
        if($request->get('phone') != null){
            $user = User::whereIn('user_type', ['customer', 'seller'])->where('banned',0)->where('phone', "+{$request['country_code']}{$request['phone']}")->first();
        }
        elseif($request->get('email') != null){
            $user = User::whereIn('user_type', ['customer', 'seller'])->where('banned',0)->where('email', $request->email)->first();
        }
        
        if($user != null){
            if(Hash::check($request->password, $user->password)){
                if($request->has('remember')){
                    auth()->login($user, true);
                }
                else{
                    auth()->login($user, false);
                }
            }
            else {
                flash(translate('Invalid email/password! or Your account may be inactive'))->warning();
             

            }
        }
        else{
            flash(translate('Invalid email/password! or Your account may be inactive'))->warning();
        }
        return back();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the customer/seller dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        if(Auth::user()->user_type == 'seller'){
            return view('seller.dashboard');
        }
        elseif(Auth::user()->user_type == 'customer'){
            return view('frontend.user.customer.dashboard');
        }
        elseif(Auth::user()->user_type == 'delivery_boy'){
            return view('delivery_boys.frontend.dashboard');
        }
        else {
            abort(404);
        }
    }

    public function profile(Request $request)
    {
        if(Auth::user()->user_type == 'delivery_boy'){
            return view('delivery_boys.frontend.profile');
        }
        else{
            return view('frontend.user.profile');
        }
    }
    public function user_profile(Request $request,$id)
    {
        // print_r($id);die;
        // $user=$id;
        $user = User::where('id', $id)->first();
        // echo'<pre>';print_r($user);die;
        return view('backend.user_profile',compact('user'));
        
    }
    public function user_profile_update(Request $request, $id)
    {
        if(env('DEMO_MODE') == 'On'){
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = User::where('id', $id)->first();
        $user->name = $request->name;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;

        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
        }
        
        $user->avatar_original = $request->photo;
        $user->save();

        flash(translate('Your Profile has been updated successfully!'))->success();
        return back();
    }

    public function userProfileUpdate(Request $request)
    {
        if(env('DEMO_MODE') == 'On'){
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;

        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
        }
        
        $user->avatar_original = $request->photo;
        $user->save();

        flash(translate('Your Profile has been updated successfully!'))->success();
        return back();
    }

    public function flash_deal_details($slug)
    {
        $flash_deal = FlashDeal::where('slug', $slug)->first();
        if($flash_deal != null)
            return view('frontend.flash_deal_details', compact('flash_deal'));
        else {
            abort(404);
        }
    }

    public function load_featured_section(){
        return view('frontend.partials.featured_products_section');
    }

    public function load_best_selling_section(){
        return view('frontend.partials.best_selling_section');
    }

    public function load_auction_products_section(){
        if(!addon_is_activated('auction')){
            return;
        }
        return view('auction.frontend.auction_products_section');
    }

    public function load_home_categories_section(){
        return view('frontend.partials.home_categories_section');
    }

    public function load_best_sellers_section(){
        return view('frontend.partials.best_sellers_section');
    }

    public function trackOrder(Request $request)
    {
        if($request->has('order_code')){
            $order = Order::where('code', $request->order_code)->first();
            if($order != null){
                return view('frontend.track_order', compact('order'));
            }
        }
        return view('frontend.track_order');
    }

    public function product(Request $request,$slug, $slug2="")
    {
        $detailedProduct  = Product::with('reviews', 'brand', 'stocks', 'user', 'user.shop')->where('auction_product', 0)->where('slug', $slug)->where('approved', 1)->first();
        
        
        if($detailedProduct != null && $detailedProduct->published){
            if($request->has('product_referral_code') && addon_is_activated('affiliate_system')) {

                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }
                Cookie::queue('product_referral_code', $request->product_referral_code, $cookie_minute);
                Cookie::queue('referred_product_id', $detailedProduct->id, $cookie_minute);

                $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            }

            /*sub product data*/
            $subProduct = $this->allSubProduct($detailedProduct->id,$detailedProduct->related_products);
          
            //echo '<pre>';print_r($detailedProduct);die;
            if($detailedProduct->digital == 1){
                return view('frontend.digital_product_details', compact('detailedProduct','subProduct'));
            }
            else {
                return view('frontend.product_details', compact('detailedProduct','subProduct','slug2'));
            }
        }
        abort(404);
    }
  

    public function allSubProduct($product_id,$dataArr = array())
    {
        $sub_products = array();
        $mainProducts = array();
        $all_product = array();
        $getallproduct = array();
        $counter = 0;
        foreach ($dataArr as $key => $value) {
            //echo "<pre>value";print_r($value);
            if ($value->parent_id == 0) {
                $sub_products = RelatedProduct::select('product_id')->where('parent_id', $value->product_id)->get();
                $mainProducts = RelatedProduct::select('product_id')->where('product_id', $value->product_id)->first();

            }else{
                $sub_products = RelatedProduct::select('product_id')->where('parent_id', $value->parent_id)->get();
                $mainProducts = RelatedProduct::select('product_id')->where('product_id', $value->parent_id)->first();
            }

            if (!empty($mainProducts)) {
                $all_product[$counter] = $mainProducts->product_id;
                //echo "<pre>mainProducts";print_r($mainProducts);
            }
            foreach ($sub_products as $key => $valuenew) {
                $counter++;
                $all_product[$counter] = $valuenew->product_id;
                //echo "<pre>sub_products";print_r($sub_products);
            }   
                    
        }
        //die;
        if (!empty($all_product)) {
           $getallproduct = Product::with('brand')->whereIn('id', $all_product)->where('published',1)->get();
        }else{
            $getallproduct = Product::with('brand')->where('id', $product_id)->where('published',1)->get();
        }
        
        
            
        return $getallproduct; 
    }
    public function shop($slug)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if($shop!=null){
            if ($shop->verification_status != 0){
                return view('frontend.seller_shop', compact('shop'));
            }
            else{
                return view('frontend.seller_shop_without_verification', compact('shop'));
            }
        }
        abort(404);
    }

    public function filter_shop($slug, $type)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if($shop!=null && $type != null){
            return view('frontend.seller_shop', compact('shop', 'type'));
        }
        abort(404);
    }

    public function all_categories(Request $request)
    {
        $categories = Category::where('level', 0)->orderBy('order_level', 'desc')->get();
        return view('frontend.all_category', compact('categories'));
    }
    
    public function all_brands(Request $request)
    { 
        //$categories = Category::all();
        return view('frontend.all_brand');
    }
	
	public function all_birthstones(Request $request)
    { 
       // $categories = Category::all();
	   $gemstones = Brand::where('gemstone_month','!=' ,'')->get();
        return view('frontend.all_birthstones', compact('gemstones'));
    }
	
	public function birthstone_gemstones($slug)
    { 
		$gemstones = Brand::where('gemstone_month',$slug)->get();
       // $categories = Category::all();
	    $slug = ucfirst($slug);
        return view('frontend.all_birthstones', compact('slug','gemstones'));
    }
	
	

    public function home_settings(Request $request)
    {
        return view('home_settings.index');
    }

    public function top_10_settings(Request $request)
    {
        foreach (Category::all() as $key => $category) {
            if(is_array($request->top_categories) && in_array($category->id, $request->top_categories)){
                $category->top = 1;
                $category->save();
            }
            else{
                $category->top = 0;
                $category->save();
            }
        }

        foreach (Brand::all() as $key => $brand) {
            if(is_array($request->top_brands) && in_array($brand->id, $request->top_brands)){
                $brand->top = 1;
                $brand->save();
            }
            else{
                $brand->top = 0;
                $brand->save();
            }
        }

        flash(translate('Top 10 categories and brands have been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    public function variant_price(Request $request)
    {
        $product = Product::find($request->id);
        $str = '';
        $quantity = 0;
        $tax = 0;
        $max_limit = 0;

        if($request->has('color')){
            $str = $request['color'];
        }

        if(json_decode($product->choice_options) != null){
            foreach (json_decode($product->choice_options) as $key => $choice) {
                if($str != null){
                    $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
                else{
                    $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
            }
        }

        $product_stock = $product->stocks->where('variant', $str)->first();
        
        $price = $product_stock->price;
        

        if($product->wholesale_product){
            $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
            if($wholesalePrice){
                $price = $wholesalePrice->price;
            }
        }

        $quantity = $product_stock->qty;
        $max_limit = $product_stock->qty;

        if($quantity >= 1 && $product->min_qty <= $quantity){
            $in_stock = 1;
        }else{
            $in_stock = 0;
        }

        //Product Stock Visibility
        if($product->stock_visibility_state == 'text') {
            if($quantity >= 1 && $product->min_qty < $quantity){
                $quantity = translate('In Stock');
            }else{
                $quantity = translate('Out Of Stock');
            }
        }

        //discount calculation
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        // taxes
        foreach ($product->taxes as $product_tax) {
            if($product_tax->tax_type == 'percent'){
                $tax += ($price * $product_tax->tax) / 100;
            }
            elseif($product_tax->tax_type == 'amount'){
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;

        return array(
            'price' => single_price($price*$request->quantity),
            'quantity' => $quantity,
            'digital' => $product->digital,
            'variation' => $str,
            'max_limit' => $max_limit,
            'in_stock' => $in_stock
        );
    }

    public function sellerpolicy(){
        $page =  Page::where('type', 'seller_policy_page')->first();
        return view("frontend.policies.sellerpolicy", compact('page'));
    }

    public function returnpolicy(){
        $page =  Page::where('type', 'return_policy_page')->first();
        return view("frontend.policies.returnpolicy", compact('page'));
    }

    public function supportpolicy(){
        $page =  Page::where('type', 'support_policy_page')->first();
        return view("frontend.policies.supportpolicy", compact('page'));
    }

    public function terms(){
        $page =  Page::where('type', 'terms_conditions_page')->first();
        return view("frontend.policies.terms", compact('page'));
    }

    public function privacypolicy(){
        $page =  Page::where('type', 'privacy_policy_page')->first();
        return view("frontend.policies.privacypolicy", compact('page'));
    }

    public function get_pick_up_points(Request $request)
    {
        $pick_up_points = PickupPoint::all();
        return view('frontend.partials.pick_up_points', compact('pick_up_points'));
    }

    public function get_category_items(Request $request){
        $category = Category::findOrFail($request->id);
        return view('frontend.partials.category_elements', compact('category'));
    }

    public function premium_package_index()
    {
        $customer_packages = CustomerPackage::all();
        return view('frontend.user.customer_packages_lists', compact('customer_packages'));
    }


    // Ajax call
    public function new_verify(Request $request)
    {
        $email = $request->email;
        if(isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = 'Email already exists!';
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }


    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        if(isUnique($email)) {
            $this->send_email_change_verification_mail($request, $email);
            flash(translate('A verification mail has been sent to the mail you provided us with.'))->success();
            return back();
        }

        flash(translate('Email already exists!'))->warning();
        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $response['status'] = 0;
        $response['message'] = 'Unknown';

        $verification_code = Str::random(32);

        $array['subject'] = 'Email Verification';
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Verify your account';
        $array['link'] = route('email_change.callback').'?new_email_verificiation_code='.$verification_code.'&email='.$email;
        $array['sender'] = Auth::user()->name;
        $array['details'] = "Email Second";

        $user = Auth::user();
        $user->new_email_verificiation_code = $verification_code;
        $user->save();

        try {
            Mail::to($email)->queue(new SecondEmailVerifyMailManager($array));

            $response['status'] = 1;
            $response['message'] = translate("Your verification mail has been Sent to your email.");

        } catch (\Exception $e) {
            // return $e->getMessage();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request){
        if($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if($user != null) {

                $user->email = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();

                auth()->login($user, true);

                flash(translate('Email Changed successfully'))->success();
                if($user->user_type == 'seller') {
                    return redirect()->route('seller.dashboard');
                }
                return redirect()->route('dashboard');
            }
        }

        flash(translate('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');

    }

    public function reset_password_with_code(Request $request){
        if (($user = User::where('email', $request->email)->where('verification_code', $request->code)->first()) != null) {
            if($request->password == $request->password_confirmation){
                $user->password = Hash::make($request->password);
                $user->email_verified_at = date('Y-m-d h:m:s');
                $user->save();
                event(new PasswordReset($user));
                auth()->login($user, true);

                flash(translate('Password updated successfully'))->success();

                if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
                {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('home');
            }
            else {
                flash("Password and confirm password didn't match")->warning();
                return redirect()->route('password.request');
            }
        }
        else {
            flash("Verification code mismatch")->error();
            return redirect()->route('password.request');
        }
    }


    public function all_flash_deals() {
        $today = strtotime(date('Y-m-d H:i:s'));

        $data['all_flash_deals'] = FlashDeal::where('status', 1)
                ->where('start_date', "<=", $today)
                ->where('end_date', ">", $today)
                ->orderBy('created_at', 'desc')
                ->get();

        return view("frontend.flash_deal.all_flash_deal_list", $data);
    }

    public function all_seller(Request $request) {
        $shops = Shop::whereIn('user_id', verified_sellers_id())
                ->paginate(15);

        return view('frontend.shop_listing', compact('shops'));
    }

    public function all_coupons(Request $request) {
        $coupons = Coupon::where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->paginate(15);
        return view('frontend.coupons', compact('coupons'));
    }

    public function inhouse_products(Request $request) {
        $products = filter_products(Product::where('added_by', 'admin'))->with('taxes')->paginate(12)->appends(request()->query());
        return view('frontend.inhouse_products', compact('products'));
    }

    public function change_product_image(Request $request)
    {
        
        $attribute_value = check_attribute_value($request->attribute_value);
        $product_variant_image = ProductVariantImage::where('variant', $attribute_value)->where('product_id', $request->product_id)->first();
        $product_video = Product::where('id', $request->product_id)->first();
        if (empty($product_variant_image)) {
           return false;
        }
         //echo "<pre>";print_r($product_video);die;
        return view('frontend.change_product_image',compact('product_variant_image','product_video'));
    }

    public function custom_store(Request $request)
    {
        //echo '<pre>';print_r($_POST);die;
        // Loop through each set of data in the request
        foreach ($request->data as $row) {
            $order = new CustomOrder;
            $order->user_id = Auth::user()->id;
            $order->gemstone = $row['gemstone'];
            $order->quantity = $row['quantity'];
            $order->metal = $row['metal'];
            $order->description = $row['description'];
            $order->custom_img  = $row['image'];

            // Save each order
            if (!$order->save()) {
                flash(translate('Something went wrong'))->error();
                return redirect()->route('home');
            }
        }

        flash(translate('Order has been successfully placed'))->success();
        return redirect()->route('home');
    }

  // In HomeController.php

  public function sampleOrders()
{
    // Fetch the logged-in user
    $user = Auth::user();

    // Check if the user is authenticated
    if ($user) {
        // Fetch paginated data from the sample_orders table for the logged-in user
        $sampleOrders = SampleOrders::where('user_id', $user->id)->paginate(10); // Adjust the number 10 to your preferred items per page

        // Pass the data to the view
        return view('frontend.user.sample', compact('sampleOrders'));
    } else {
        // Handle case if the user is not authenticated
        return redirect()->route('user.login')->with('error', 'You must be logged in to view sample orders.');
    }
}

public function cancelOrder($id)
{
    try {
        // Find the order by ID
        $order = SampleOrders::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.']);
        }

        if ($order->status == 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Order is already cancelled.']);
        }

        // Update the status
        $order->status = 'cancelled';
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}





    public function sample_order_store(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            // Add flash message and return JSON
            flash(translate('Sorry! The action is not permitted in demo mode.'))->error();
            return response()->json([
                'success' => false,
                'message' => translate('Sorry! The action is not permitted in demo mode.')
            ]);
        }
    
        // Validate incoming request
        $request->validate([
            'id' => 'required|integer',
            'quantity' => 'required|integer',
            'attribute_id_4' => 'required|string', // Metal
            'attribute_id_3' => 'required|string', // Gemstone
        ]);
    
        // Create a new SampleOrders record
        $order = new SampleOrders;
        $order->order_id = $this->generateOrderId(); // Generate unique order ID
        $order->user_id = Auth::user()->id;
        $order->product_id = $request->input('id');
        $order->size = $request->input('attribute_id_3'); // Gemstone
        $order->gemstone = $request->input('gemstone');
        $order->quantity = $request->input('quantity');
        $order->metal = $request->input('attribute_id_4'); // Metal
    
        if ($order->save()) {
            // Flash success message
            flash(translate('Sample order has been successfully placed!'))->success();
            return response()->json([
                'success' => true,
                'message' => translate('Sample order has been successfully placed!')
            ]);
        } else {
            // Flash error message
            flash(translate('Something went wrong while saving the sample order.'))->error();
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong while saving the sample order.')
            ]);
        }
    }
    

    
    private function generateOrderId()
    {
        // Get the last order ID from the SampleOrders table
        $lastOrder = SampleOrders::orderBy('id', 'desc')->first();
    
        // Generate a unique order number based on the last order ID, or start at 1000 if there are no orders
        $nextOrderNumber = $lastOrder ? (int)substr($lastOrder->order_id, 6) + 1 : 4565; // Starting number 4565
    
        // Return the formatted order ID
        return 'sample_' . $nextOrderNumber;
    }
    

}
