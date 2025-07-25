@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->meta_title }}@stop

@section('meta_description'){{ $detailedProduct->meta_description }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency" content="{{ \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">


    <!-- Schema Markup for Product -->
   <!-- Schema Markup for Product -->
<script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "{{ $detailedProduct->meta_title }}",
      "image": [
        "{{ uploaded_asset($detailedProduct->meta_img) }}"
      ],
      "description": "{{ $detailedProduct->meta_description }}",
      "sku": "{{ optional($detailedProduct->stocks->first())->sku ?? '' }}",
      "brand": {
        "@type": "Brand",
        "name": "{{ $detailedProduct->brand->name ?? 'Pandbimports' }}"
      },
      "offers": {
        "@type": "Offer",
        "url": "{{ route('product', $detailedProduct->slug) }}",
        
        "priceCurrency": "{{ optional(\App\Models\Currency::find(get_setting('system_default_currency')))->code ?? 'U.S. Dollar' }}",

        "price": "{{ single_price($detailedProduct->unit_price) }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "https://schema.org/{!! ($detailedProduct->stock_visibility_state == 'qty' && $detailedProduct->qty > 0) ? 'InStock' : 'OutOfStock' !!}",
        "seller": {
          "@type": "Organization",
          "name": "Pandbimports"
        }
      }
    }
    </script>
    
@endsection

@section('content')
<?php //echo'<pre>'; print_r($slug2); die;?>
    <section class="mb-4 pt-3 product-detail-page">
        <div class="container">
            <div class="bg-white shadow-sm rounded p-3">
                <div class="row" >
                    <!-- <div class="col-xl-6 col-lg-6 mb-4" id="loader" style="display:block;"><img
                            class="img-fluid lazyload"
                            src="{{ static_asset('assets/img/Preloader.gif') }}"
                        ></div>  -->
                    <div class="col-xl-6 col-lg-6 mb-4 product_img_all_new" id="product_img_all">
                        <div class="sticky-top z-3 row gutters-10">
                            @php
                                $photos = explode(',', $detailedProduct->photos);
                            @endphp
                            <?php //echo '<pre>';print_r($photos);die;?>
                            <div class="col order-1 order-md-2"> 
                                <div class="aiz-carousel product-gallery main-product-detail" data-nav-for='.product-gallery-thumb' data-fade='true' data-auto-height='true' style="display:none;">
                                    @foreach ($photos as $key => $photo)  
                                                                      
                                        <div class="carousel-box img-zoom rounded">
                                        {{$photo}}
                                            <img
                                                class="img-fluid lazyload"
                                                src="{{ !empty($photo) ? uploaded_asset($photo) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                                data-src="{{ !empty($photo) ? uploaded_asset($photo) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                    @endforeach
                                    @foreach ($detailedProduct->stocks as $key => $stock)

                                        @if ($stock->image != null)
                                            <div class="carousel-box img-zoom rounded">
                                                <img
                                                    class="img-fluid lazyload"
                                                    src="{{ !empty($photo) ? uploaded_asset($photo) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                                    data-src="{{ !empty($stock->image) ? uploaded_asset($stock->image) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 col-md-auto w-md-80px order-2 order-md-1 mt-3 mt-md-0 main-product-detail" style="display:none;">
                                <div class="aiz-carousel product-gallery-thumb" data-items='5' data-nav-for='.product-gallery' data-vertical='true' data-vertical-sm='false' data-focus-select='true' data-arrows='true'>
                                    @foreach ($photos as $key => $photo)
                                    <div class="carousel-box c-pointer border p-1 rounded">
                                        <img
                                            class="lazyload mw-100 size-50px mx-auto"
                                            src="{{ !empty($photo) ? uploaded_asset($photo) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                            data-src="{{ !empty($photo) ? uploaded_asset($photo) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                        >
                                    </div>
                                    @endforeach
                                    @foreach ($detailedProduct->stocks as $key => $stock)
                                        @if ($stock->image != null)
                                            <div class="carousel-box c-pointer border p-1 rounded" data-variation="{{ $stock->variant }}">
                                                <img
                                                    class="lazyload mw-100 size-50px mx-auto"
                                                    src="{{ !empty($stock->image) ? uploaded_asset($stock->image) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                                    data-src="{{ !empty($stock->image) ? uploaded_asset($stock->image) : uploaded_asset($detailedProduct->thumbnail_img) }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                   

                    <div class="col-xl-6 col-lg-6 ">
                        <div class="text-left">
                            <h1 class="mb-2 fs-20 fw-600">
                                {{ $detailedProduct->getTranslation('name') }}
                            </h1>
                            
                            <div class="row align-items-center">
                                <div class="col-12">
                                    @php
                                        $total = 0;
                                        $total += $detailedProduct->reviews->count();
                                    @endphp

                                    @if($total >= 0)
                                    <span class="rating">
                                        {{ renderStarRating($detailedProduct->rating) }}
                                    </span>
                                    <span class="ml-1 opacity-50">({{ $total }} {{ translate('reviews')}})</span>
                                    @else
                                    <span class="rating custom-display">
                                        {{ renderStarRating($detailedProduct->rating) }}
                                    </span>
                                    <span class="ml-1 opacity-50 custom-display">({{ $total }} {{ translate('reviews')}})</span>                                    
                                    @endif

                                </div>
                                @if ($detailedProduct->est_shipping_days)
                                <div class="col-auto ml">
                                    <small class="mr-2 opacity-50">{{ translate('Estimate Shipping Time')}}: </small>{{ $detailedProduct->est_shipping_days }} {{  translate('Days') }}
                                </div>
                                @endif
                            </div>
                            @if($detailedProduct->gemstone_size)
                            <hr>
                            @endif

                            <div class="row align-items-center">
                                <div class="col-auto display-none">
                                    <small class="mr-2 opacity-50">{{ translate('Sold by')}}: </small><br>
                                    @if ($detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1)
                                        <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset">{{ $detailedProduct->user->shop->name }}</a>
                                    @else
                                        {{  translate('Inhouse product') }}
                                    @endif
                                </div>
                                @if (get_setting('conversation_system') == 1)
                                    <div class="col-auto  display-none">
                                        <button class="btn btn-sm btn-soft-primary" onclick="show_chat_modal()">{{ translate('Message Seller')}}</button>
                                    </div>
                                @endif

                                @if ($detailedProduct->brand != null)
									<div class="row no-gutters pb-3 display-none" id="">
										<div class="col-sm-6">
											<div class="opacity-50 my-2">Gemstone:</div>
										</div>
										<div class="col-sm-6">
											<div class="">
											<a href="{{ route('products.gemstone',$detailedProduct->brand->slug) }}">
												
												<!--<img src="{{ uploaded_asset($detailedProduct->brand->logo) }}" alt="{{ $detailedProduct->brand->getTranslation('name') }}" height="30"> -->
												<strong id="" class="h4 fw-600 text-primary">
												
												{{ $detailedProduct->brand->getTranslation('name') }}</strong>
											</a>
											</div>
										</div>
									</div>
								
                                   
                                @endif
								
								 </div>
								<div class="row clearfix">
									<?php if(!empty($detailedProduct->product_weight)){ ?>
									<div class="col-12 product-info-box">
										<div class="col-3 float-left">
												<span class="heading">Product Weight:</span>
										</div>
										<div class="col-6 float-left">
											<span class="value"><?php echo $detailedProduct->product_weight; ?></span>
										</div>
									</div>
									<?php } ?>
									
									@if ($detailedProduct->brand != null)
									<div class="col-12 product-info-box">
										<div class="col-3 float-left">
												<span class="heading">Gemstone:</span>
										</div>
										<div class="col-6 float-left">
											<span class="value"> {{ $detailedProduct->brand->getTranslation('name') }}</span>
										</div>
									</div>
									 @endif
									
									<?php if(!empty($detailedProduct->gemstone_size)){ ?>
									<div class="col-12 product-info-box">
										<div class="col-3 float-left">
												<span class="heading">Gemstone Size:</span>
										</div>
										<div class="col-6 float-left">
											<span class="value"><?php echo $detailedProduct->gemstone_size; ?></span>
										</div>
									</div>
									<?php } ?>
									
									<?php if(!empty($detailedProduct->gemstone_weight)){ ?>
									<div class="col-12 product-info-box">
										<div class="col-3 float-left">
												<span class="heading">Gemstone Weight:</span>
										</div>
										<div class="col-6 float-left">
											<span class="value"><?php echo $detailedProduct->gemstone_weight; ?></span>
										</div>
									</div>
									<?php } ?>
								</div>
                            @if($detailedProduct->gemstone_size)
                            <hr>
                            @endif

                            @if ($detailedProduct->wholesale_product)
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Min Qty') }}</th>
                                            <th>{{ translate('Max Qty') }}</th>
                                            <th>{{ translate('Unit Price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detailedProduct->stocks->first()->wholesalePrices as $wholesalePrice)
                                            <tr>
                                                <td>{{ $wholesalePrice->min_qty }}</td>
                                                <td>{{ $wholesalePrice->max_qty }}</td>
                                                <td>{{ single_price($wholesalePrice->price) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                @if(home_price($detailedProduct) != home_discounted_price($detailedProduct))
                                    <div class="row no-gutters mt-3  display-none">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Price')}}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="fs-20 opacity-60">
                                                <del>
                                                    {{ home_price($detailedProduct) }}
                                                    @if($detailedProduct->unit != null)
                                                        <span>/{{ $detailedProduct->getTranslation('unit') }}</span>
                                                    @endif
                                                </del>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row no-gutters my-2 display-none">
                                        <div class="col-sm-2">
                                            <div class="opacity-50">{{ translate('Discount Price')}}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ home_discounted_price($detailedProduct) }}
                                                </strong>
                                                @if($detailedProduct->unit != null)
                                                    <span class="opacity-70">/{{ $detailedProduct->getTranslation('unit') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row no-gutters mt-3  display-none">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Price')}}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ home_discounted_price($detailedProduct) }}
                                                </strong>
                                                @if($detailedProduct->unit != null)
                                                    <span class="opacity-70">/{{ $detailedProduct->getTranslation('unit') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                                <div class="row no-gutters mt-4">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{  translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="d-inline-block rounded px-2 bg-soft-primary border-soft-primary border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr class="display-none">

                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">
                                <input type="hidden" name="gemstone" value="{{ isset($detailedProduct->brand) ? $detailedProduct->brand->slug : '' }}">

                                

                                <div class="row no-gutters mt-4 gemstone-list-product">
                                
                                    <div class="col-sm-12">
                                        <?php foreach ($subProduct as $key => $value){ 
                                            $active=($detailedProduct->slug==$value->slug)?'active':''; 
                                            $url=($detailedProduct->slug==$value->slug)?"javascript:void(0)":"route('product', $value->slug) "; ?>
                                            <?php //echo'<pre'; print_r($detailedProduct);  ?>
                                            @if($detailedProduct->brand_id)
                                            <div class="col-sm-3 col-4 float-left" style="padding-left: 0px;    padding-right: 5px;">
                                                <a href="{{ ($detailedProduct->slug==$value->slug)?'javascript:void(0)':route('product', $value->slug) }}" class="d-block text-reset {{$active}}" style="border: 1px solid #ededed;padding: 5px;text-align: center;">
                                                <img class="img-fit lazyload h-xxl-80px h-auto h-xl-20px h-50px" src="{{ uploaded_asset($value->thumbnail_img) }}" alt="">
                                                @if(isset($value->brand->name))
                                                <p>{{$value->brand->name}}</p>
                                                
                                                @endif
                                                </a>    
                                            </div>
                                            @endif
                                        <?php } ?>                                    
                                    </div>
                                </div>
                            

                                <hr>
                                @if ($detailedProduct->choice_options != null)
                                    @foreach (json_decode($detailedProduct->choice_options) as $key => $choice)
                                    <?php 
                                    $attribute_name = \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name'); ?>
                                    <div class="row no-gutters">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ $attribute_name }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="aiz-radio-inline">
                                                <?php sort($choice->values); ?>
                                                @foreach ($choice->values as $key => $value)
                                                <label class="aiz-megabox pl-0 mr-2">
                                                    <input
                                                        type="radio" 
                                                        onchange="change_product_image(this)"
                                                        data-attribute_name = "{{ $attribute_name }}"
                                                        data-product_id = "{{ $detailedProduct->id }}"
                                                        class="attribute_id"
                                                        name="attribute_id_{{ $choice->attribute_id }}"
                                                        value="{{ $value }}"
                                                        @if($key == 0) checked @endif
                                                    >
                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                                        {{ $value }}
                                                    </span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    @endforeach
                                @endif

                                @if (count(json_decode($detailedProduct->colors)) > 0)
                                    <div class="row no-gutters">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Color')}}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="aiz-radio-inline">
                                                @foreach (json_decode($detailedProduct->colors) as $key => $color)
                                                <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip" data-title="{{ \App\Models\Color::where('code', $color)->first()->name }}">
                                                    <input
                                                        type="radio"
                                                        name="color"
                                                        value="{{ \App\Models\Color::where('code', $color)->first()->name }}"
                                                        @if($key == 0) checked @endif   
                                                    >
                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                        <span class="size-30px d-inline-block rounded" style="background: {{ $color }};"></span>
                                                    </span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                @endif
                                <div class="row no-gutters">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('SKU')}}:</div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="opacity-90 my-2 ">
                                        {{optional($detailedProduct->stocks->first())->sku ? substr(optional($detailedProduct->stocks->first())->sku, 0, 7) : '' }}
                                    </div>
                                       
                                    </div>

                                </div>
                                <!-- Quantity + Add to cart -->
                                <div class="row no-gutters">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('Quantity')}}:</div>
                                    </div>@php
                                                $qty = 0;
                                                foreach ($detailedProduct->stocks as $key => $stock) {
                                                    $qty += $stock->qty;
                                                }
                                            @endphp
                                    <div class="col-sm-10">
                                        <div class="product-quantity d-flex align-items-center">
                                            @if($qty == 0 || !empty($slug2))
                                            <div class="row no-gutters align-items-center aiz-plus-minus mr-3" style="width: 130px;">
                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-type="minus" data-field="quantity" >
                                                    <i class="las la-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" id="out-stock-quantity" class="col border-0 text-center flex-grow-1 fs-16 input-number" placeholder="1" value="{{get_setting('out_stock_minimum_order')}}" min="{{get_setting('out_stock_minimum_order')}}" max="10" oofsq="1" lang="en">
                                                <button class="btn  col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-type="plus" data-field="quantity">
                                                    <i class="las la-plus"></i>
                                                </button>
                                            </div> 
                                            @else
                                            <div class="row no-gutters align-items-center aiz-plus-minus mr-3" style="width: 130px;">
                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-type="minus" data-field="quantity" disabled="">
                                                    <i class="las la-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" class="col border-0 text-center flex-grow-1 fs-16 input-number quantity_instock" placeholder="1" value="{{ $detailedProduct->min_qty }}" min="{{ $detailedProduct->min_qty }}" oofsq="0" max="10" lang="en">
                                                <button class="btn  col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-type="plus" data-field="quantity">
                                                    <i class="las la-plus"></i>
                                                </button>
                                            </div>
                                            @endif


                                            @if(!$slug2 && $qty != 0)
                                            <div class="avialable-amount opacity-60" >
                                                @if($detailedProduct->stock_visibility_state == 'quantity')
                                                (<span id="available-quantity">{{ $qty }}</span> {{ translate('available')}})
                                                @elseif($detailedProduct->stock_visibility_state == 'text' && $qty >= 1)
                                                    (<span id="available-quantity">{{ translate('In Stock') }}</span>)
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                @if (isset(Auth::user()->id) || $detailedProduct->is_price_show)
                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('Total Price')}}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="product-price">
                                            <strong id="chosen_price" class="h4 fw-600 text-primary">

                                            </strong>
                                        </div>
                                    </div>
                                </div>
                                 @endif
                            </form>
                            <div class="mt-3">
                                <!-- @if(empty($slug2) && $qty != 0)
                                <p>If you want to order more than the available stock <a href="{{ route('product.outStockDetail', ['slug'=>$detailedProduct->slug, 'slug2'=>'out-stock-product-detail']) }}">Click Here</a></p>
                                @elseif($slug2='out-stock-product-detail' || $qty == 0)
                                <p> <b>OUT OF STOCK MINIMUM ORDER QUANTITY IS - {{get_setting('out_stock_minimum_order')}} <b></p>
                                @endif -->
                                @if ($detailedProduct->external_link != null)
                                    <a type="button" class="btn btn-primary buy-now fw-600" href="{{ $detailedProduct->external_link }}">
                                        <i class="la la-share"></i> {{ translate($detailedProduct->external_link_btn)}}
                                    </a>
                                @else
                                    @if(Auth::check())
                                        @if(empty($slug2) && $qty != 0)
                                        <button type="button" class="btn btn-primary btn-soft-primary mr-2 cstm_add_cart_btn add-to-cart fw-600  cart_or_oso" onclick="addToCart()" >
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Add to cart')}}</span>
                                        </button>

                                        {{-- <button type="button" class="btn btn-primary btn-soft-primary mr-2 cstm_add_cart_btn add-to-cart fw-600  cart_or_oso" onclick="addToCart()" >
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Sample Order')}}</span>
                                        </button> --}}

                                        <button type="button" class="btn btn-primary btn-soft-primary mr-2 cstm_add_cart_btn add-to-cart fw-600 cart_or_oso" onclick="addSampleOrder()">
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Sample Order')}}</span>
                                        </button>
                                        

                                        @elseif($slug2='out-stock-product-detail' || $qty == 0)
                                        <button type="button" class="btn btn-primary btn-soft-primary mr-2 cstm_add_cart_btn add-to-cart fw-600  cart_or_oso" onclick="addToCart()">
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Bulk cart')}}</span>
                                        </button>

                                        <button type="button" class="btn btn-primary btn-soft-primary mr-2 cstm_add_cart_btn add-to-cart fw-600 cart_or_oso" onclick="addSampleOrder()">
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Sample Order')}}</span>
                                        </button>
                                        
                                        @endif
                                        @if(empty($slug2) && $qty != 0)
                                        <a href="{{ route('product.outStockDetail', ['slug'=>$detailedProduct->slug, 'slug2'=>'out-stock-product-detail']) }}">
                                            <button type="button" class="btn btn-primary btn-soft-primary mr-2 fw-600 cstm_add_cart_btn cart_or_oso">
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate(' Order In Bulk')}}</span>
                                            </button>
                                        </a>
                                        @elseif($slug2='out-stock-product-detail' || $qty == 0)
                                        <p> <b>OUT OF STOCK MINIMUM ORDER QUANTITY IS - {{get_setting('out_stock_minimum_order')}} <b></p>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600 custom_add_cart_btn" onclick="showLoginCartModal()">
                                            <i class="las la-shopping-bag"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Add to cart')}}</span>
                                        </button>
                                    @endif

                                @endif
     
                                <button type="button" class="btn btn-secondary fw-600 out-of-stock d-none" style="margin-right: 2%;" disabled>
                                    <i class="la la-cart-arrow-down"></i> {{ translate('Out of Stock')}}
                                </button>
                                <button type="button" class="btn btn-primary fw-600 out-of-stock d-none"  data-toggle="modal" data-target="#out-stock-order" >
                                    <i class="la la-shopping-cart"></i> {{ translate('Buy Now')}}
                                </button>
                            </div>



                            <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    <!-- Add to wishlist button -->
                                    <!-- <button type="button" class="btn pl-0 btn-link fw-600" onclick="addToWishList({{ $detailedProduct->id }})">
                                        {{ translate('Add to wishlist')}}
                                    </button> -->
                                    <!-- Add to compare button -->
                                    <button type="button" class="btn btn-link btn-icon-left fw-600 display-compare" onclick="addToCompare({{ $detailedProduct->id }})">
                                        {{ translate('Add to compare')}}
                                    </button>
                                    @if(Auth::check() && addon_is_activated('affiliate_system') && (\App\Models\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\Models\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                        @php
                                            if(Auth::check()){
                                                if(Auth::user()->referral_code == null){
                                                    Auth::user()->referral_code = substr(Auth::user()->id.Str::random(10), 0, 10);
                                                    Auth::user()->save();
                                                }
                                                $referral_code = Auth::user()->referral_code;
                                                $referral_code_url = URL::to('/product').'/'.$detailedProduct->slug."?product_referral_code=$referral_code";
                                            }
                                        @endphp
                                        <div>
                                            <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary" data-attrcpy="{{ translate('Copied')}}" onclick="CopyToClipboard(this)" data-url="{{$referral_code_url}}">{{ translate('Copy the Promote Link')}}</button>
                                        </div>
                                    @endif
                                </div>
                            </div>


                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Refund')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank"> 
                                            @if ($refund_sticker != null) 
                                                <img src="{{ uploaded_asset($refund_sticker) }}" height="36"> 
                                            @else 
                                                <img src="{{ static_asset('assets/img/refund-sticker.jpg') }}" height="36"> 
                                            @endif</a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2" target="_blank">{{ translate('View Policy') }}</a>
                                    </div>
                                </div>
                            @endif
                            <!-- <div class="row no-gutters mt-4">
                                
                                <div class="col-sm-12">
                                    <div class="aiz-share"></div>
                                </div>
                            </div> -->

                            

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                <div class="col-xl-3 order-1 order-xl-0">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->shop != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">
                                @if ($detailedProduct->user->shop->verification_status)
                                    <div class="absolute-top-right p-2 bg-white z-1">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 287.5 442.2" width="22" height="34">
                                            <polygon style="fill:#F8B517;" points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 "/>
                                            <circle style="fill:#FBD303;" cx="143.8" cy="143.8" r="143.8"/>
                                            <circle style="fill:#F8B517;" cx="143.8" cy="143.8" r="93.6"/>
                                            <polygon style="fill:#FCFCFD;" points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                            60,116.6 124.1,116.6 "/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="opacity-50 fs-12 border-bottom">{{ translate('Sold by')}}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->shop->name }}
                                    @if ($detailedProduct->user->shop->verification_status == 1)
                                        <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                    @else
                                        <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                    @endif
                                </a>
                                <div class="location opacity-70">{{ $detailedProduct->user->shop->address }}</div>
                                <div class="text-center border rounded p-2 mt-3">
                                    <div class="rating">
                                        @if ($total > 0)
                                            {{ renderStarRating($detailedProduct->user->shop->rating) }}
                                        @else
                                            {{ renderStarRating(0) }}
                                        @endif
                                    </div>
                                    <div class="opacity-60 fs-12">({{ $total }} {{ translate('customer reviews')}})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block btn btn-soft-primary rounded-0">{{ translate('Visit Store')}}</a>
                                </div>
                                <div class="col">
                                    <ul class="social list-inline mb-0">
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook" target="_blank">
                                                <i class="lab la-facebook-f opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google" target="_blank">
                                                <i class="lab la-google opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter" target="_blank">
                                                <i class="lab la-twitter opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube" target="_blank">
                                                <i class="lab la-youtube opacity-60"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
				<?php /*
                    <div class="bg-white rounded shadow-sm mb-3">
                        <div class="p-3 border-bottom fs-16 fw-600">
                            {{ translate('Top Selling Products')}}
                        </div>
                        <div class="p-3">
                            <ul class="list-group list-group-flush">
                                @foreach (filter_products(\App\Models\Product::where('user_id', $detailedProduct->user_id)->orderBy('num_of_sale', 'desc'))->limit(6)->get() as $key => $top_product)
                                <li class="py-3 px-0 list-group-item border-light">
                                    <div class="row gutters-10 align-items-center">
                                        <div class="col-5">
                                            <a href="{{ route('product', $top_product->slug) }}" class="d-block text-reset">
                                                <img
                                                    class="img-fit lazyload h-xxl-110px h-xl-80px h-120px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($top_product->thumbnail_img) }}"
                                                    alt="{{ $top_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="col-7 text-left">
                                            <h4 class="fs-13 text-truncate-2">
                                                <a href="{{ route('product', $top_product->slug) }}" class="d-block text-reset">{{ $top_product->getTranslation('name') }}</a>
                                            </h4>
                                            <div class="rating rating-sm mt-1">
                                                {{ renderStarRating($top_product->rating) }}
                                            </div>
                                            <div class="mt-2">
                                                <span class="fs-17 fw-600 text-primary">{{ home_discounted_base_price($top_product) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                */ ?>
				
				</div>
				
                <div class="col-xl-12 order-0 order-xl-1">
                    <div class="bg-white mb-3 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Description')}}</a>
                            @if($detailedProduct->video_link != null)
                                <a href="#tab_default_2" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Video')}}</a>
                            @endif
                            @if($detailedProduct->pdf != null)
                                <a href="#tab_default_3" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Downloads')}}</a>
                            @endif
                                <a href="#tab_default_4" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Reviews')}}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                        <?php echo $detailedProduct->getTranslation('description'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab_default_2">
                                <div class="p-4">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ get_url_params($detailedProduct->video_link, 'v') }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                                            <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_default_3">
                                <div class="p-4 text-center ">
                                    <a href="{{ uploaded_asset($detailedProduct->pdf) }}" class="btn btn-primary">{{  translate('Download') }}</a>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_default_4">
                                <div class="p-4">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($detailedProduct->reviews as $key => $review)
                                            @if($review->user != null)
                                            <li class="media list-group-item d-flex">
                                                <span class="avatar avatar-md mr-3">
                                                    <img
                                                        class="lazyload"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        @if($review->user->avatar_original !=null)
                                                            data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                        @else
                                                            data-src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        @endif
                                                    >
                                                </span>
                                                <div class="media-body text-left">
                                                    <div class="d-flex justify-content-between">
                                                        <h3 class="fs-15 fw-600 mb-0">{{ $review->user->name }}</h3>
                                                        <span class="rating rating-sm">
                                                            @for ($i=0; $i < $review->rating; $i++)
                                                                <i class="las la-star active"></i>
                                                            @endfor
                                                            @for ($i=0; $i < 5-$review->rating; $i++)
                                                                <i class="las la-star"></i>
                                                            @endfor
                                                        </span>
                                                    </div>
                                                    <div class="opacity-60 mb-2">{{ date('d-m-Y', strtotime($review->created_at)) }}</div>
                                                    <p class="comment-text">
                                                        {{ $review->comment }}
                                                    </p>
                                                </div>
                                            </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    @if(count($detailedProduct->reviews) <= 0)
                                        <div class="text-center fs-18 opacity-70">
                                            {{  translate('There have been no reviews for this product yet.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded shadow-sm">
                        <div class="border-bottom p-3">
                            <h3 class="fs-16 fw-600 mb-0">
                                <span class="mr-4">{{ translate('Related products')}}</span>
                            </h3>
                        </div>
                        <div class="p-3">
                            <div class="aiz-carousel gutters-5 half-outside-arrow" data-items="3" data-xl-items="3" data-lg-items="3"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-infinite='true'>
                                <?php
                                $related_product_id= array();
                                $related_product_id = getRelatedProductId($detailedProduct->id);
                                    // print_r($related_product_id)
                                    // die('sgfs');
                                    if(!empty($related_product_id)){
                                ?>
                                @foreach (filter_products(\App\Models\Product::whereIN('id', $related_product_id))->limit(10)->get() as $key => $related_product)
                                <div class="carousel-box">
                                    <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                        <div class="">
                                            <a href="{{ route('product', $related_product->slug) }}" class="d-block">
                                                <img
                                                    class="img-fit lazyload mx-auto h-140px h-md-370px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($related_product->thumbnail_img) }}"
                                                    alt="{{ $related_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="p-md-3 p-2 text-left">
                                            @if (isset(Auth::user()->id))

                                            <div class="fs-17">
                                                @if(home_base_price($related_product) != home_discounted_base_price($related_product))
                                                    <del class="fw-400 opacity-50 mr-1">{{ home_base_price($related_product) }}</del>
                                                @endif
                                                <span class="fw-400 text-primary">{{ home_discounted_base_price($related_product) }}</span>
                                            </div>
                                             @endif
                                            <div class="rating rating-sm mt-1">
                                                {{ renderStarRating($related_product->rating) }}
                                            </div>
                                            <h3 class="fw-400 fs-20 text-truncate-2 lh-1-4 mb-0 h-35px">
                                                <a href="{{ route('product', $related_product->slug) }}" class="d-block text-reset">{{ $related_product->getTranslation('name') }}</a>
                                            </h3>
                                            @if (addon_is_activated('club_point'))
                                                <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                                    {{ translate('Club Point') }}:
                                                    <span class="fw-700 float-right">{{ $related_product->earn_point }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this product')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title" value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone')}}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{  translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ translate('Password')}}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{  translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ translate('Forgot password?')}}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ translate('Dont have an account?')}}</p>
                            <a href="{{ route('user.registration') }}">{{ translate('Register Now')}}</a>
                        </div>
                        @if(get_setting('google_login') == 1 ||
                            get_setting('facebook_login') == 1 ||
                            get_setting('twitter_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ translate('Or Login With')}}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if(get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BUY NOW MODAL -->
<div class="modal fade" id="out-stock-order" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" >Confirm</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="font-size: 120%;">
      <i class="las la-exclamation-triangle" style="font-size: 900% !important;margin-left: 31%;color: #c09578;" ></i><br>
        This is a special request, out of stock order hence can take <span >upto 10 to 12 business days to be shipped <sup>*T&C apply</sup> </span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"  data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="buyNow()">Proceed</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script') 
    <script type="text/javascript">
        /*$(document).ready(function() {
            getVariantPrice();
            $("#loader").css("display",'block');
            $.each($('.attribute_id'), function(){
                if($(this).prop("checked") == true /*&& $(this).data('attribute_name') == 'Materials'){
                  
                    change_product_image(this);
                }else{
                    
                }

            });
              
           //change_product_image(this); 

    	});*/

        $(document).ready(function() {
            // Trigger the variant price check
            getVariantPrice();

            // Show the loader while loading images
            $("#loader").css("display", 'block');

            // Loop through attribute checkboxes
            var attributeSelected = false;
            $.each($('.attribute_id'), function() {
                if ($(this).prop("checked") == true) {
                    // If an attribute is checked, change the product image
                    change_product_image(this);
                    attributeSelected = true;
                }
            });

            // If no attribute is selected, display the default product image
            if (!attributeSelected) {
                // Hide loader and display default images
                $("#loader").css("display", 'none');
                $(".main-product-detail").css("display", 'block');
                $(".product_img_all_new").css("display", 'block');
            }
        });

        $(function () {
          $('[data-toggle="tooltip"]').tooltip()
        })

        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
            }
            $temp.remove();
            // if (document.selection) {
            //     var range = document.body.createTextRange();
            //     range.moveToElementText(document.getElementById(containerid));
            //     range.select().createTextRange();
            //     document.execCommand("Copy");

            // } else if (window.getSelection) {
            //     var range = document.createRange();
            //     document.getElementById(containerid).style.display = "block";
            //     range.selectNode(document.getElementById(containerid));
            //     window.getSelection().addRange(range);
            //     document.execCommand("Copy");
            //     document.getElementById(containerid).style.display = "none";

            // }
            // AIZ.plugins.notify('success', 'Copied');
        }
        function show_chat_modal(){
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }


        /*function change_product_image(el){
            //alert('test');
            var attribute_value = $(el).val();
            var attribute_name = $(el).data('attribute_name');
            var product_id = $(el).data('product_id');

            if (attribute_name == 'Metals') {
                alert('test');
                //$("#loader").css("display",'block');
                $("#overlay").css("display",'block');
                $("#PleaseWait").css("display",'block');
                //$(".product_img_all_new").css("display",'none');
                $.ajax({
                   type:"POST",
                   headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                   url:'{{ route('products.change-product-image') }}',
                   data:{attribute_value:attribute_value, attribute_name:attribute_name, product_id:product_id},
                   success: function(data) {
                 
                        if (data != false) {
                            //$("#loader").css("display",'none');
                            $("#overlay").css("display",'none');
                            $("#PleaseWait").css("display",'none');
                            $(".product_img_all_new").css("display",'block');

                            $('#product_img_all').html(null);
                            $('#product_img_all').html(data);
                            

                            $(".main-product-detail").css("display",'block');
                            
                        }else{
                             //$("#loader").css("display",'none');
                             $("#overlay").css("display",'none');
                             $("#PleaseWait").css("display",'none');
                            $(".main-product-detail").css("display",'block');
                            $(".product_img_all_new").css("display",'block');
                        }

                        
                   },
                  error: function(e) {
                    
                        //$("#loader").css("display",'none');
                        $("#overlay").css("display",'none');
                        $("#PleaseWait").css("display",'none');
                        $(".main-product-detail").css("display",'block');
                        $(".product_img_all_new").css("display",'block');
                  }
                        
               });

            }else{
                       
                        //$("#loader").css("display",'none');
                        $(".main-product-detail").css("display",'block');
            }
        }*/
        function change_product_image(el) {
            var attribute_value = $(el).val();
            var attribute_name = $(el).data('attribute_name');
            var product_id = $(el).data('product_id');

            // Check for 'Metals' attribute and send AJAX request
            if (attribute_name === 'Metals') {
                $("#overlay").css("display", 'block');
                $("#PleaseWait").css("display", 'block');

                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('products.change-product-image') }}',
                    data: { attribute_value: attribute_value, attribute_name: attribute_name, product_id: product_id },
                    success: function(data) {
                        $("#overlay").css("display", 'none');
                        $("#PleaseWait").css("display", 'none');

                        if (data != false) {
                            // Update product images
                            $('#product_img_all').html(null);
                            $('#product_img_all').html(data);
                        }

                        // Show product images
                        $(".main-product-detail").css("display", 'block');
                        $(".product_img_all_new").css("display", 'block');
                    },
                    error: function(e) {
                        // On error, hide overlay and show default images
                        $("#overlay").css("display", 'none');
                        $("#PleaseWait").css("display", 'none');
                        $(".main-product-detail").css("display", 'block');
                        $(".product_img_all_new").css("display", 'block');
                    }
                });
            } else {
                // If the attribute is not 'Metals', show default product images
                $(".main-product-detail").css("display", 'block');
            }
        }
    </script>

    
    
@endsection
