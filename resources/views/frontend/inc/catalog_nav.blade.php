<?php //print_r(Auth::user()->user_type);die;?>
@if(get_setting('topbar_banner') != null)
<div class="position-relative top-banner removable-session z-1035 d-none" data-key="top-banner" data-value="removed">
    <a href="{{ get_setting('topbar_banner_link') }}" class="d-block text-reset">
        <img src="{{ uploaded_asset(get_setting('topbar_banner')) }}" class="w-100 mw-100 h-50px h-lg-auto img-fit">
    </a>
    <button class="btn text-white absolute-top-right set-session" data-key="top-banner" data-value="removed" data-toggle="remove-parent" data-parent=".top-banner">
        <i class="la la-close la-2x"></i>
    </button>
</div>
@endif
<!-- Top Bar -->

<?php 
	$menu_categories = \App\Models\Category::select(['name','slug'])->where('level', 0)->orderBy('order_level','asc')->get();
	//$menu_gemstones = \App\Models\Brand::select(['name','slug','id'])->where('active',1)->orderBy('name','asc')->get();

                // Fetch brands that have published products directly in the query
    $menu_gemstones = \App\Models\Brand::select(['name', 'slug', 'id'])
    ->where('active', 1)
    ->whereHas('products', function($query) {
        $query->where('published', 1);
    })
    ->orderBy('name', 'asc')
    ->get();

    $menu_months = array('January','February','March','April','May','June','July','August','September','October','November','December');
?>
<!-- END Top Bar -->


<header class="@if(get_setting('header_stikcy') == 'on') sticky-header sticky-top @endif mobile-display-not" style="display:none">

    @if ( get_setting('header_menu_labels') !=  null )
        <div class="bg-white top_last_header border-top border-gray-200 py-2">
            <div class="container">
			<div  class="col-xl-12">
			<div class="col-auto col-3 col-xl-4  float-left  ">
				<a class="d-block py-15px mr-3 ml-0" href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-60px" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-60px" height="40">
                        @endif
                    </a>
				</div>
				<div class="col-4 col-xl-8 float-left">
               <!-- <ul class="list-inline  py-20px  mb-0 pl-0 mobile-hor-swipe text-center">
                    @foreach (json_decode( get_setting('header_menu_labels'), true) as $key => $value)
                    <li class="list-inline-item mr-0">
                        <a href="{{ json_decode( get_setting('header_menu_links'), true)[$key] }}" class="opacity-60 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                            {{ translate($value) }}
                        </a>
                    </li>
                    @endforeach
					
				
                </ul> -->
				
				<div class="top_navigation megamenusip  py-20px ">
   
    <ul class="exo-menu ">
      <li><a href="javascript:void(0)">Home</a></li>
      <li class="mega-drop-down"><a href="javascript:void(0)"><i class="fa fa-list"></i> Shop </a>
        <div class="animated fadeIn mega-menu">
		
		
          <div class="mega-menu-wrap">
            <div class="row">
			<?php if(!empty($menu_categories)){ ?>
				<div class="col-xl-2">
					<h4 class="row mega-title">Categories</h4>
					<ul class="cabeza">
					<?php foreach($menu_categories as $menu_category){ ?>
                        @if (Auth::user())
					    <li><a href="{{ route('products.category', $menu_category->slug) }}">{{ $menu_category->getTranslation('name') }}</a></li>
                        
                        @else
					    <li><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_category->getTranslation('name') }}</a></li>
                        @endif
                      <?php } ?>
					</ul>
					
                </div>
			<?php } ?>
			<div class="col-xl-2">

                    <?php if(!empty($menu_months)){ ?>
                        
                            <h4 class="row mega-title">Birthstones</h4>
                            <ul class="cabeza">
                            <?php foreach($menu_months as $menu_month){ ?>
                                @if (Auth::user())
                                <li><a href="{{ route('birthstones.gemstone_month', strtolower($menu_month)) }}">{{ $menu_month  }}</a></li>
                                
                                @else
                                <li><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_month  }}</a></li>
                                @endif
                                <?php } ?>
                            </ul>
                    <?php } ?>                
            </div>
			<?php $gemstone_sticky="Gemstone";
				if(!empty($menuGemstonesArr)){ 
					foreach($menuGemstonesArr as $menu_gemstones){
			?>
				<div class="col-xl-2">
					<h4 class="row mega-title"><?php echo $gemstone_sticky; ?></h4>
					<ul class="cabeza">
					<?php foreach($menu_gemstones as $menu_gemstone){ ?>
                        @if (Auth::user())
					        <li><a href="{{ route('products.gemstone', $menu_gemstone->slug) }}">{{ $menu_gemstone->getTranslation('name') }}</a></li>
                        
                        @else
					        <li><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_gemstone->getTranslation('name') }}</a></li>
                        @endif
					<?php } ?>
					</ul>
                </div>
				<?php
                $gemstone_sticky=" ";
                } }  ?>
			
			
			
             </div>
          </div>  
        </div>
      </li>
     <?php //if(!empty($menu_months)){ ?>
      <!-- <li class="drop-down"><a href="javascript:void(0)"><i class="fa fa-cogs"></i> Birthstones</a>
        <ul class="drop-down-ul animated fadeIn"> -->
		
        <?php // foreach($menu_months as $menu_month){ ?>
           <?php // @if (Auth::user())
		       // <li class="flyout-right"><a href="{{ route('birthstones.gemstone_month', strtolower($menu_month)) }}">{{ $menu_month .' Birthstone'; }}</a>
            
          //  @else
		     //   <li class="flyout-right"><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_month .' Birthstone'; }}</a>
          //  @endif
          ?>
          <?php 
			// $birth_gemstones = \App\Models\Brand::where('gemstone_month',$menu_month)->get(); 
			
			// if(!empty($birth_gemstones)){
		?>
			<!-- <ul class="animated fadeIn"> -->
				<?php // foreach($birth_gemstones as $birth_gemstone){ ?>
				<?php	//@if (Auth::user())
        				//	<li><a href="{{ route('products.gemstone', $birth_gemstone->slug) }}">{{ $birth_gemstone->getTranslation('name') }}</a></li>
                        //
                        //@else
        				//	<li><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $birth_gemstone->getTranslation('name') }}</a></li>
                        //@endif
                        ?>
				<?php // } ?>
          <!-- </ul>  -->
			<?php //} ?>
		  <!-- </li> -->
		<?php //} ?>
                
                                      
        <!-- </ul>      -->
        <!-- </li>  -->
	  <?php // } ?>
      <li class="drop-down"><a href="{{route('pages.contact-us')}}">Contact Us</a></li>
      <!-- <li class="drop-down"><a href="{{route('pages.customize-product')}}">Customize Product</a></li> -->
    @if(Auth::check())
    <li class="drop-down"><a href="{{route('pages.customize-product')}}">Customize Product</a></li>
    @endif 
    <li class="drop-down"><a href="{{route('pages.our-process')}}">Our Process</a></li>

	</ul>
   </div>
   
				</div>
				
				 <div class="col-auto col-5  py-15px col-xl-4 pl-0 pr-3 d-flex align-items-center  ">
					<div class="d-none   d-lg-block ml-3 mr-0 display-compare">
						<div class="" id="compare">
							@include('frontend.partials.compare')
						</div>
					</div>

					<!--<div class="d-none d-lg-block ml-3 mr-0">
						<div class="" id="wishlist">
							@include('frontend.partials.wishlist')
						</div>
					</div>

					<div class="d-none d-lg-block  align-self-stretch ml-3 mr-0" data-hover="dropdown">
						<div class="nav-cart-box dropdown h-100" id="cart_items">
							@include('frontend.partials.cart')
						</div>
					</div> -->
				</div>
			
				
            </div>
            </div>
        </div>
    @endif
</header>

<div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div id="order-details-modal-body">

            </div>
        </div>
    </div>
</div>

@section('script')
    <script type="text/javascript">
        
        function show_order_details(order_id)
        {
            $('#order-details-modal-body').html(null);

            if(!$('#modal-size').hasClass('modal-lg')){
                $('#modal-size').addClass('modal-lg');
            }

            $.post('{{ route('orders.details') }}', { _token : AIZ.data.csrf, order_id : order_id}, function(data){
                $('#order-details-modal-body').html(data);
                $('#order_details').modal();
                $('.c-preloader').hide();
                AIZ.plugins.bootstrapSelect('refresh');
            });
        }

		
		
    </script>
@endsection
