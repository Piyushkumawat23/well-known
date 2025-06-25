<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
use Artisan;
use Cache;
use App\Models\UsersImport;
use PDF;
use Excel;
use Auth;

use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $user_type = null;
        $users = User::with('salesperson')->where('user_type', '!=','admin')/*->where('email_verified_at', '!=', null)*/->orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $users->where(function ($q) use ($sort_search){
                $q->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');
            });
        }
        if ($request->user_type != null) {
            $user_type = $request->user_type;
            if($request->user_type == 'salesperson')
            {
                $users = $users->where('is_salesperson', 1);            
            }else{
                $users = $users->where('is_salesperson', 0);

            }
        }

        $all_salesperson = User::select('id','name')->where('is_salesperson',1)->get();
        
        $users = $users->paginate(15);

        //echo "<pre>";print_r($users);die;
        return view('backend.customer.customers.index', compact('users', 'sort_search', 'user_type','all_salesperson'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.customer.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name'          => 'required',
    //         'email'         => 'required|unique:users|email',
    //         'phone'         => 'required|unique:users',
    //     ]);
        
    //     $response['status'] = 'Error';
        
    //     $user = User::create($request->all());
        
    //     $customer = new Customer;
        
    //     $customer->user_id = $user->id;
    //     $customer->save();
        
    //     if (isset($user->id)) {
    //         $html = '';
    //         $html .= '<option value="">
    //                     '. translate("Walk In Customer") .'
    //                 </option>';
    //         foreach(Customer::all() as $key => $customer){
    //             if ($customer->user) {
    //                 $html .= '<option value="'.$customer->user->id.'" data-contact="'.$customer->user->email.'">
    //                             '.$customer->user->name.'
    //                         </option>';
    //             }
    //         }
            
    //         $response['status'] = 'Success';
    //         $response['html'] = $html;
    //     }
        
    //     echo json_encode($response);
    // }


    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        // Create User
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password), // secure password
        ]);
        flash(__('User created successfully!'))->success(); // using flash message

        return redirect()->route('customers.index');
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
    public function destroy($id)
    {
        User::destroy($id);
        flash(translate('Customer has been deleted successfully'))->success();
        return redirect()->route('customers.index');
    }

    public function customers_info($id)
    {
        $orders = Order::where('user_id', $id)->paginate(15);
        $heading = User::where('id', $id)->first();
        // echo($heading);die;
        // echo'<pre>'; print_r($heading);die;
        return view('backend.customer.customers.customer_information', compact('orders','heading'));
    }
    
    public function bulk_customer_delete(Request $request) {
        if($request->id) {
            foreach ($request->id as $customer_id) {
                $this->destroy($customer_id);
            }
        }
        
        return 1;
    }

    public function login($id)
    {
        $user = User::findOrFail(decrypt($id));

        auth()->login($user, true);

        return redirect()->route('dashboard');
    }

    public function ban($id) {
        $user = User::findOrFail(decrypt($id));

        if($user->banned == 1) {
            $user->banned = 0;
            flash(translate('Customer UnBanned Successfully'))->success();
        } else {
            $user->banned = 1;
            flash(translate('Customer Banned Successfully'))->success();
        }

        $user->save();
        
        return back();
    }

    /**
     * update_salesperson the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_salesperson(Request $request)
    {
        $products =array();
        $subMain =array();
        $mainid = $request->id;        
        $selected_id = $request->selected_id;        

        $allSalespersonData = User::select('name', 'id')->where('is_salesperson', 1)->get()->toArray();

        return view('backend.customer.customers.update_salesperson', compact('allSalespersonData','mainid','selected_id'));
        
    }  
    /**
     * add_salesperson the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */

    public function add_salesperson(Request $request)
    {

        if (isset($request->selected) && !empty($request->selected) && !empty($request->mainid)) {                
            $user = User::where('id', $request->mainid)->first();
            //echo '<pre>';print_r($user);die;
            if (!empty($user)) {
                $user->salesperson_id = $request->selected;
                $user->save(); 
            }
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;

        }

        return 0;
        
    }    


    public function bulkindex()
    {
        if (Auth::user()->user_type == 'seller') {
            if(Auth::user()->shop->verification_status){
                return view('seller.product_bulk_upload.index');
            }
            else{
                flash('Your shop is not verified yet!')->warning();
                return back();
            }
        }
        elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('backend.customer.bulk_upload.index');
            // backend.customer.customers.update_salesperson
        }
    }

    
    public function bulk_users_upload(Request $request)
    {
        try {
            if ($request->hasFile('bulk_file')) {
                $import = new UsersImport;
                Excel::import($import, $request->file('bulk_file'));
            }

            return back()->with('success', __('Users uploaded successfully!'));
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = 'Row '.$failure->row().': '.implode(', ', $failure->errors());
            }

            return back()->with('error', implode('<br>', $messages));
        } catch (\Exception $e) {
            return back()->with('error', __('Upload failed: ') . $e->getMessage());
        }
    }
}
