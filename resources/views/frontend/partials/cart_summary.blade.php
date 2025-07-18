<div class="card border-0 shadow-sm rounded">
    <div class="card-header">
        <h3 class="fs-16 fw-600 mb-0">{{translate('Summary')}}</h3>
        <div class="text-right">
            <span class="badge badge-inline badge-primary">

                {{ count($carts_del_info) }} 
                {{translate('Items')}}
            </span>
            @php $subtotal_for_min_order_amount = 500; @endphp
            @foreach ($carts_del_info as $key => $cartItem)
                @php $subtotal_for_min_order_amount += $cartItem['price'] * $cartItem['quantity']; @endphp
            @endforeach
            @if(get_setting('minimum_order_amount_check') == 1 && $subtotal_for_min_order_amount < get_setting('minimum_order_amount'))
                <span class="badge badge-inline badge-primary">
                    {{translate('Minimum Order Amount').' '.single_price(get_setting('minimum_order_amount'))}} 
                </span>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if (addon_is_activated('club_point'))
            @php
                $total_point = 0;
            @endphp
            @foreach ($carts as $key => $cartItem)
                @php
                    $product = \App\Models\Product::find($cartItem['product_id']);
                    $total_point += $product->earn_point * $cartItem['quantity'];
                @endphp
            @endforeach
            
            <div class="rounded px-2 mb-2 bg-soft-primary border-soft-primary border">
                {{ translate("Total Club point") }}:
                <span class="fw-700 float-right">{{ $total_point }}</span>
            </div>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th class="product-name">{{translate('Product')}}</th>
                    <th class="product-total text-right">{{translate('Total')}}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $product_shipping_cost = 0;
                    $shipping_region = (isset($shipping_info['city']) && $shipping_info['city'] != '') ? $shipping_info['city'] : '';
                @endphp    
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                        $subtotal += $cartItem['price'] * $cartItem['quantity'];
                        $tax += $cartItem['tax'] * $cartItem['quantity'];
                        $product_shipping_cost = $cartItem['shipping_cost'];

                        if(isset($_GET['shipping_type']) && $_GET['shipping_type'] == "priority"){
                            $shipping += getShippingCost($carts, $key);
                        }else{
                            $shipping += $product_shipping_cost;
                        }
                        
                        
                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variant'] != null) {
                            $product_name_with_choice = $product->getTranslation('name').' - '.$cartItem['variant'];
                        }
                    @endphp
                    <tr class="cart_item">
                        <td class="product-name">
                            {{ $product_name_with_choice }}
                            <strong class="product-quantity">
                                × {{ $cartItem['quantity'] }}
                            </strong>
                        </td>
                        <td class="product-total text-right">
                            <span class="pl-4 pr-0">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <input type="hidden" id="sub_total" value="{{ $subtotal }}">
        <table class="table">

            <tfoot>
                <tr class="cart-subtotal">
                    <th>{{translate('Subtotal')}}</th>
                    <td class="text-right">
                        <span class="fw-600">{{ single_price($subtotal) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{translate('Tax')}}</th>
                    <td class="text-right">
                        <span class="font-italic">{{ single_price($tax) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{translate('Total Shipping')}}</th>
                    <td class="text-right">
                        <span class="font-italic">{{ single_price($shipping) }}</span>
                    </td>
                </tr>
                <?php $wholesaleCommissionAmount=wholesaleCommissionAmount($subtotal);?>
                @if ($wholesaleCommissionAmount > 0)
                <tr class="cart-shipping">
                    <th>{{translate('Wholesale Discount')}}</th>
                    <td class="text-right">
                        <span class="font-italic">{{ '$'.number_format($wholesaleCommissionAmount,2,'.') }}</span>
                    </td>
                </tr>
                @endif
                @if (Session::has('club_point'))
                    <tr class="cart-shipping">
                        <th>{{translate('Redeem point')}}</th>
                        <td class="text-right">
                            <span class="font-italic">{{ single_price(Session::get('club_point')) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($carts->sum('discount') > 0)
                    <tr class="cart-shipping">
                        <th>{{translate('Coupon Discount')}}</th>
                        <td class="text-right">
                            <span class="font-italic">{{ single_price($carts->sum('discount')) }}</span>
                        </td>
                    </tr>
                @endif

                @php
                    $total = ($subtotal+$tax+$shipping)-$wholesaleCommissionAmount;
                    if(Session::has('club_point')) {
                        $total -= Session::get('club_point');
                    }
                    if ($carts->sum('discount') > 0){
                        $total -= $carts->sum('discount');
                    }
                @endphp

                <tr class="cart-total">
                    <th><span class="strong-600">{{translate('Total')}}</span></th>
                    <td class="text-right">
                        <strong><span>{{ single_price($total) }}</span></strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        @if (addon_is_activated('club_point'))
            @if (Session::has('club_point'))
                <div class="mt-3">
                    <form class="" action="{{ route('checkout.remove_club_point') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <div class="form-control">{{ Session::get('club_point')}}</div>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Remove Redeem Point')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        @endif

        @if (Auth::check() && get_setting('coupon_system') == 1)
            @php 
                $coupon_discount = $carts->sum('discount'); 
                $coupon_code = null;
            @endphp
            @foreach ($carts as $key => $cartItem)
                @if($cartItem->coupon_applied == 1 && $cartItem->discount > 0)
                    @php
                        $coupon_code = $cartItem->coupon_code;
                        break;
                    @endphp
                @endif
            @endforeach
            @if ($coupon_discount > 0 && $coupon_code)
                <div class="mt-3">
                    <form class="" id="remove-coupon-form" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <div class="form-control">{{ $coupon_code }}</div>
                            <div class="input-group-append">
                                <button type="button" id="coupon-remove" class="btn btn-primary">{{translate('Change Coupon')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="mt-3">
                    <form class="" id="apply-coupon-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                        <div class="input-group">
                            <input type="text" class="form-control" name="code" onkeydown="return event.key != 'Enter';" placeholder="{{translate('Have coupon code? Enter here')}}" required>
                            <div class="input-group-append">
                                <button type="button" id="coupon-apply" class="btn btn-primary">{{translate('Apply')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        @endif

    </div>
</div>
