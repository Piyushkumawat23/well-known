<form id="add_Child_product" method="POST">
<div class="card">
    <div class="card-body">    
        <div class="form-group row">
            <div class="col-md-8">
                <select class="form-control aiz-selectpicker" onchange="add_Child(this)" name="child_product[]" id="child_product" data-selected-text-format="count" data-live-search="true" multiple data-placeholder="{{ translate('Choose Product') }}" data-mainid="{{$mainid}}">
                    @foreach($allProductsData as $key => $allProduct)
                        <option value="{{$allProduct['id']}}">{{$allProduct['name']}}</option>
                    @endforeach
                </select>
                <input type="hidden" name="mainid" value="{{$mainid}}">
            </div>
        </div>
    </div>
</div>
</form>

<div class="card">

    <form class="" id="sort_products" action="" method="GET">
        <div class="card-body">
            <table class="table aiz-table mb-0 op-remove">
                <thead>
                    <tr>
                        <!--<th data-breakpoints="lg">#</th>-->
                        <th>{{translate('Name')}}</th>                        
                        <th data-breakpoints="sm">{{translate('Info')}}</th>
                        <th data-breakpoints="lg">{{translate('Total Stock')}}</th>
                        <!--<th data-breakpoints="lg">{{translate('Todays Deal')}}</th>-->

                        <th data-breakpoints="sm" class="text-right">{{translate('Options')}}</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                        
                    <tr>
                        
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col-auto">
                                    <img src="{{ uploaded_asset($product->thumbnail_img)}}" alt="Image" class="size-50px img-fit">
                                </div>
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ $product->getTranslation('name') }}</span>
                                </div>
                            </div>
                        </td>
                       
                        <td>
                            <strong>{{translate('Num of Sale')}}:</strong> {{ $product->num_of_sale }} {{translate('times')}} </br>
                            <strong>{{translate('Base Price')}}:</strong> {{ single_price($product->unit_price) }} </br>
                            <strong>{{translate('Rating')}}:</strong> {{ $product->rating }} </br>
                        </td>
                        <td>
                            @php
                                $qty = 0;
                                if($product->variant_product) {
                                    foreach ($product->stocks as $key => $stock) {
                                        $qty += $stock->qty;
                                        echo $stock->variant.' - '.$stock->qty.'<br>';
                                    }
                                }
                                else {
                                    //$qty = $product->current_stock;
                                    $qty = optional($product->stocks->first())->qty;
                                    echo $qty;
                                }
                            @endphp
                            @if($qty <= $product->low_stock_quantity)
                                <span class="badge badge-inline badge-danger">Low</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="delete_child(this)" data-id="{{ $product->id }}" data-mainid="{{ $mainid }}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>                        
                       <!-- <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_todays_deal(this)" value="{{ $product->id }}" type="checkbox" <?php if ($product->todays_deal == 1) echo "checked"; ?> >
                                <span class="slider round"></span>
                            </label>
                        </td> -->
                        
                        
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <?php if (!empty($products)) { ?>
                

            <div class="aiz-pagination">
                {{ $products->appends(request()->input())->links() }}
            </div>
                        <?php } ?>
        </div>
    </form>
</div>
    <script src="{{ static_asset('assets/js/aiz-core.js') }}" ></script>
