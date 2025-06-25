
<div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-2 has-transition bg-white">
    @if(discount_in_percentage($product) > 0)
        <span class="badge-custom">{{ translate('OFF') }}<span class="box ml-1 mr-0">&nbsp;{{discount_in_percentage($product)}}%</span></span>
    @endif

    <div class="position-relative">
        @php
            $product_url = route('product', $product->slug);
            if($product->auction_product == 1) {
                $product_url = route('auction-product', $product->slug);
            }
        @endphp

        @if (Auth::user())
        <a href="{{ $product_url }}" class="d-block product_detail_page">
            <img
                class="img-fit lazyload mx-auto h-140px <?php echo (isset($page_type) && $page_type == "listing") ? 'h-md-280px' : 'h-md-370px'; ?>"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                alt="{{  $product->getTranslation('name')  }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        @else
        <a href="javascript:void(0);" onclick="showLoginCartModal()" class="d-block product_detail_page">
            <img
                class="img-fit lazyload mx-auto h-140px <?php echo (isset($page_type) && $page_type == "listing") ? 'h-md-280px' : 'h-md-370px'; ?>"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                alt="{{  $product->getTranslation('name')  }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        @endif
        @if ($product->wholesale_product)
            <span class="absolute-bottom-left fs-11 text-white fw-600 px-2 lh-1-8" style="background-color: #455a64">
                {{ translate('Wholesale') }}
            </span>
        @endif
        <div class="absolute-top-right aiz-p-hov-icon">
            <a href="javascript:void(0)" class="display-none" onclick="addToWishList({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                <i class="la la-heart-o"></i>
            </a>
            <a href="javascript:void(0)" onclick="addToCompare({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left" class="display-compare">
                <i class="las la-sync"></i>
            </a>
            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to cart') }}" data-placement="left" class="display-none">
                <i class="las la-shopping-cart"></i>
            </a>
        </div>
    </div>

    <div class="p-md-3 p-2 text-left">
        @if (Auth::user())
        <h3 class="fw-400 fs-16 text-truncate-2 lh-1-4  mb-3 h-40px">
            <a href="{{ $product_url }}" class="d-block text-reset">{{  $product->getTranslation('name')  }}</a>
        </h3>
        @else
        <h3 class="fw-400 fs-18 text-truncate-2 lh-1-4 mb-1 h-20px">
            <a href="javascript:void(0);" onclick="showLoginCartModal()" class="d-block text-reset">{{  $product->getTranslation('name')  }}</a>
        </h3>
        @endif
        <div class="rating rating-sm mt-1 custom-display">
            {{ renderStarRating($product->rating) }}
        </div>
        @if (isset(Auth::user()->id))
        <div class="fs-17">
            @if(home_base_price($product) != home_discounted_base_price($product))
                <del class="fw-400 opacity-50 mr-1">{{ home_base_price($product) }}</del>
            @endif
            <span class="fw-400 text-primary">{{ home_discounted_base_price($product) }}</span>
        </div>
        @endif
        @if (addon_is_activated('club_point'))
            <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                {{ translate('Club Point') }}:
                <span class="fw-700 float-right">{{ $product->earn_point }}</span>
            </div>
        @endif
    </div>
</div>
