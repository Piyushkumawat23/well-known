@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ $mainproduct->name .'-'.translate('Related Product')}}</h1>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Filter') }}</h5>
            </div>
            
            
            <div class="col-md-2 ml-auto">
                    <label class="aiz-checkbox">{{ translate('Is Related Product') }}
                        <input type="checkbox" class="check-one" name="is_related_product" value="1" @if ($is_related_product == 1) checked @endif onchange="sort_products(this)">
                        <span class="aiz-square-check"></span>
                    </label>
            </div>
            <div class="col-md-2 ml-auto" id="category">
                <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true" onchange="sort_products(this)">
                    <option value="">{{ translate('Select Category') }}</option>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @if ($category->id == $category_id) selected @endif>{{ $category->getTranslation('name') }}</option>
                        @foreach ($category->childrenCategories as $childCategory)
                            @include('categories.child_category', ['child_category' => $childCategory])
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>
        
    </form>
</div>

<div class="card">

    <form class="" id="sormain" action="" method="GET">
        <input type="hidden" id="mainid" value="{{$mainproduct->id}}">
        <div class="card-body">
            <table class="table aiz-table mb-0 op-remove">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">#</th>
                        <th>{{translate('Name')}}</th>                        
                        <th data-breakpoints="sm">{{translate('Info')}}</th>
                        <th data-breakpoints="lg">{{translate('Total Stock')}}</th>
                        <!--<th data-breakpoints="lg">{{translate('Todays Deal')}}</th>-->

                        <th data-breakpoints="sm" class="text-right">{{translate('Options')}}</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                      <?php $color_tr = ($product['is_selected'] == 1)?'background-color: #bbf1bb;':''; ?>
                    <tr style="{{$color_tr}}">
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$product->id}}" onchange="update_related(this)">
                                    
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
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
                            @if($product['is_selected'] == 1)
                            <a class="btn btn-soft-danger btn-icon btn-circle btn-sm" href="{{route('products.related-product-destroy', [$product->id, $mainproduct->id])}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                            @endif
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
@endsection
@section('script')
    <script type="text/javascript">

        function update_related(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            var mainid=$('#mainid').val();
            $.post('{{ route('products.update-related') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status, mainid:mainid}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Products Add successfully') }}');
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function sort_products(el){
            $('#sort_products').submit();
        }
    </script>
@endsection    
