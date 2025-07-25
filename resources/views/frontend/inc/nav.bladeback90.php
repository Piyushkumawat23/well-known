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
	$menu_gemstones = \App\Models\Brand::select(['name','slug'])->where('active',1)->orderBy('name','asc')->get();
    $menu_months = array('January','February','March','April','May','June','July','August','September','October','November','December');
?>
<?php // echo'<pre>'; print_r($menu_gemstones); die;?>

<div class="top-navbar bg-white border-bottom border-soft-secondary z-1035">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col">
                <ul class="list-inline d-flex justify-content-between justify-content-lg-start  mb-0">
					<li class="welcome_text">Welcome to {{ get_setting('site_name') }}</li>
                    @if(get_setting('show_language_switcher') == 'on')
                    <li class="list-inline-item dropdown mr-3" id="lang-change">
                        @php
                            if(Session::has('locale')){
                                $locale = Session::get('locale', Config::get('app.locale'));
                            }
                            else{
                                $locale = 'en';
                            }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown" data-display="static">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class="mr-2 lazyload" alt="{{ \App\Models\Language::where('code', $locale)->first()->name }}" height="11">
                            <span class="opacity-60">{{ \App\Models\Language::where('code', $locale)->first()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-left">
                            @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                                <li>
                                    <a href="javascript:void(0)" data-flag="{{ $language->code }}" class="dropdown-item @if($locale == $language) active @endif">
                                        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-1 lazyload" alt="{{ $language->name }}" height="11">
                                        <span class="language">{{ $language->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif

                    @if(get_setting('show_currency_switcher') == 'on')
                    <li class="list-inline-item dropdown ml-auto ml-lg-0 mr-0" id="currency-change">
                        @php
                            if(Session::has('currency_code')){
                                $currency_code = Session::get('currency_code');
                            }
                            else{
                                $currency_code = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;
                            }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-60" data-toggle="dropdown" data-display="static">
                            {{ \App\Models\Currency::where('code', $currency_code)->first()->name }} {{ (\App\Models\Currency::where('code', $currency_code)->first()->symbol) }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left">
                            @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                                <li>
                                    <a class="dropdown-item @if($currency_code == $currency->code) active @endif" href="javascript:void(0)" data-currency="{{ $currency->code }}">{{ $currency->name }} ({{ $currency->symbol }})</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>

            <div class="col-5 text-right d-none d-lg-block">
                <ul class="list-inline mb-0 h-100 d-flex justify-content-end align-items-center">
                    @if (get_setting('contact_phone'))
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="tel:{{ get_setting('helpline_number') }}" class="text-reset d-inline-block opacity-60 py-2">
                                <i class="la la-phone"></i>
                                <span>{{ translate('Contact Phone :')}}</span>  
                                <span>{{ get_setting('contact_phone') }}</span>    
                            </a>
                        </li>
                    @endif 
                    @auth
                        @if(isAdmin())
                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                <a href="{{ route('admin.dashboard') }}" class="text-reset d-inline-block opacity-60 py-2">{{ translate('My Panel')}}</a>
                            </li>
                        @else

                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0 dropdown">
                                <a class="dropdown-toggle no-arrow text-reset" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                    <span class="">
                                        <span class="position-relative d-inline-block">
                                            <i class="las la-bell fs-18"></i>
                                            @if(count(Auth::user()->unreadNotifications) > 0)
                                                <span class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                            @endif
                                        </span>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg py-0">
                                    <div class="p-3 bg-light border-bottom">
                                        <h6 class="mb-0">{{ translate('Notifications') }}</h6>
                                    </div>
                                    <div class="px-3 c-scrollbar-light overflow-auto " style="max-height:300px;">
                                        <ul class="list-group list-group-flush" >
                                            @forelse(Auth::user()->unreadNotifications as $notification)
                                                <li class="list-group-item">
                                                    @if($notification->type == 'App\Notifications\OrderNotification')
                                                        @if(Auth::user()->user_type == 'customer')
                                                        <a href="javascript:void(0)" onclick="show_purchase_history_details({{ $notification->data['order_id'] }})" class="text-reset">
                                                            <span class="ml-2">
                                                                {{translate('Order code: ')}} {{$notification->data['order_code']}} {{ translate('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                            </span>
                                                        </a>
                                                        @elseif (Auth::user()->user_type == 'seller')
                                                            @if(Auth::user()->id == $notification->data['user_id'])
                                                                <a href="javascript:void(0)" onclick="show_purchase_history_details({{ $notification->data['order_id'] }})" class="text-reset">
                                                                    <span class="ml-2">
                                                                        {{translate('Order code: ')}} {{$notification->data['order_code']}} {{ translate('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                                    </span>
                                                                </a>
                                                            @else
                                                                <a href="javascript:void(0)" onclick="show_order_details({{ $notification->data['order_id'] }})" class="text-reset">
                                                                    <span class="ml-2">
                                                                        {{translate('Order code: ')}} {{$notification->data['order_code']}} {{ translate('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                                    </span>
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="list-group-item">
                                                    <div class="py-4 text-center fs-16">
                                                        {{ translate('No notification found') }}
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="text-center border-top">
                                        <a href="{{ route('all-notifications') }}" class="text-reset d-block py-2">
                                            {{translate('View All Notifications')}}
                                        </a>
                                    </div>
                                </div>
                            </li>

                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                @if (Auth::user()->user_type == 'seller')
                                    <a href="{{ route('seller.dashboard') }}" class="text-reset d-inline-block opacity-60 py-2">{{ translate('My Panel')}}</a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="text-reset d-inline-block opacity-60 py-2">{{ translate('My Panel')}}</a>
                                @endif
                            </li>
                        @endif
                        <li class="list-inline-item">
                            <a href="{{ route('logout') }}" class="text-reset d-inline-block opacity-60 py-2">{{ translate('Logout')}}</a>
                        </li>
                    @else
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="{{ route('user.login') }}" class="text-reset d-inline-block opacity-60 py-2">{{ translate('Login')}}</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ route('user.registration') }}" class="text-reset d-inline-block opacity-60 py-2">{{ translate('Registration')}}</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- END Top Bar -->
<header class="@if(get_setting('header_stikcy') == 'on')  @endif z-1020 bg-white border-bottom shadow-sm middle_header_area">
    <div class="position-relative logo-bar-area z-1">
        <div class="container">
            <div class="d-flex align-items-center">
				<div class="col-auto col-xl-4 pl-0 pr-3  pt-3  d-flex align-items-center  mobile-display-not">
					<div class="home_contact">
						<div class="contact_box contact_box_address float-left">
							<label>Location</label>
							<p>{{ get_setting('contact_address',null,App::getLocale()) }}</p>
						</div> 
						<div class="contact_box float-left">
						<label>Contact</label> 
						<p><a href="mailto:{{ get_setting('contact_email') }}">{{ get_setting('contact_email') }}</a></p>
						</div>
					</div>
				</div>
                <div class="col-auto col-xl-4 pl-4 pr-3 d-flex align-items-center">
                    <a class="d-block py-10px mr-3 ml-0 " href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-70px logo-mid" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-100px" height="40">
                        @endif
                    </a>

                    @if(Route::currentRouteName() != 'home')
                        <div class="d-none d-xl-block align-self-stretch category-menu-icon-box ml-auto mr-0 display-none">
                            <div class="h-100 d-flex align-items-center" id="category-menu-icon">
                                <div class="dropdown-toggle navbar-light bg-light h-40px w-50px pl-2 rounded border c-pointer">
                                    <span class="navbar-toggler-icon"></span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="d-none d-lg-none ml-3 mr-0">
                    <div class="nav-search-box">
                        <a href="javascript:void(0)" class="nav-box-link">
                            <i class="la la-search la-flip-horizontal d-inline-block nav-box-icon"></i>
                        </a>
                    </div>
                </div>
			 <div class="col-auto col-xl-4 pl-0 pr-3 d-flex align-items-center header-cart-wishlist-area">
                <div class="d-none d-lg-block ml-3 mr-0 display-compare">
                    <div class="" id="compare">
                        @include('frontend.partials.compare')
                    </div>
                </div>

                <div class="d-none d-lg-block ml-3 mr-0">
                    <div class="" id="wishlist">
                        @include('frontend.partials.wishlist')
                    </div>
                </div>

                <div class="d-none d-lg-block  align-self-stretch ml-3 mr-0" data-hover="dropdown">
                    <div class="nav-cart-box dropdown h-100" id="cart_items">
                        @include('frontend.partials.cart')
                    </div>
                </div>
            </div>

            </div>
        </div>
        @if(Route::currentRouteName() != 'home')
        <div class="hover-category-menu position-absolute w-100 top-100 left-0 right-0 d-none z-3" id="hover-category-menu">
            <div class="container">
                <div class="row gutters-10 position-relative">
                    <div class="col-lg-3 position-static">
                        @include('frontend.partials.category_menu')
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @if ( get_setting('header_menu_labels') !=  null )
        <div class="bg-white top_last_header border-top border-gray-200 mobile-display-not py-2">
            <div class="container">
			<div  class="col-xl-12">
				<div class="col-12 col-xl-9 float-left menu_area">
               <!-- <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-center">
                    @foreach (json_decode( get_setting('header_menu_labels'), true) as $key => $value)
                    <li class="list-inline-item mr-0">
                        <a href="{{ json_decode( get_setting('header_menu_links'), true)[$key] }}" class="opacity-60 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                            {{ translate($value) }}
                        </a>
                    </li>
                    @endforeach
                </ul> -->
				
				<div class="top_navigation mobile-display-not megamenusip">
   
    <ul class="exo-menu">
      <li><a href="{{ route('home') }}">Home</a></li>
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
					<!-- <h4 class="row mega-title">Categories</h4>
					<ul class="cabeza"> -->
                    <?php if(!empty($menu_months)){ ?>
						
						<h4 class="row mega-title">Birthstones</h4>
						<ul class="cabeza">
						<?php foreach($menu_months as $menu_month){ ?>
                          @if (Auth::user())
						    <li><a href="{{ route('birthstones.gemstone_month', strtolower($menu_month)) }}" style="margin-right: -5%;">{{ $menu_month .' Birthstone'; }}</a></li>
                            
                            @else
						    <li><a  href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_month .' Birthstone'; }}</a></li>
                            @endif
                        <?php } ?>
						</ul>
				<?php } ?>
                    
            </div>
			
			<?php 
				$menuGemstonesArr = array();
                        // echo $menu_gemstone->active; echo'<br>';
				if(!empty($menu_gemstones)){
					$i = 0;
					foreach($menu_gemstones as $k => $menu_gemstone){
                        // echo $menu_gemstone->active; echo'<br>';
                        if($k % 22 == 0 ){
                    
						$i++;
					}
                
					$menuGemstonesArr[$i][$k] = $menu_gemstone;
					}
				}
			
			?>
			<?php 
                $title_gemstone= 'Gemstones';
				if(!empty($menuGemstonesArr)){ 
					foreach($menuGemstonesArr as $menu_gemstones){
			?>
				<div class="col-xl-2">
					<h4 class="row mega-title"><?php echo$title_gemstone ?></h4>
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
                $title_gemstone = " ";
                } }  ?>
			
			
			
             </div>
          </div>  
        </div>
      </li>
	  <?php// if(!empty($menu_months)){ ?>
      <!-- <li class="drop-down"><a href="javascript:void(0)"><i class="fa fa-cogs"></i> Birthstones</a>
        <ul class="drop-down-ul animated fadeIn"> -->
		
        <?php// foreach($menu_months as $menu_month){ ?>
            <!-- @if (Auth::user())
            <li class="flyout-right"><a href="{{ route('birthstones.gemstone_month', strtolower($menu_month)) }}">{{ $menu_month .' Birthstone'; }}</a>
            
            @else
            <li class="flyout-right"><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_month .' Birthstone'; }}</a>
            @endif -->
          <?php

			// $birth_gemstones = \App\Models\Brand::where('gemstone_month',$menu_month)->get(); 
			
			// if(!empty($birth_gemstones)){
		?>
			<!-- <ul class="animated fadeIn"> -->
				<?php //foreach($birth_gemstones as $birth_gemstone){ ?>
                    <?php //-- @if (Auth::user())
        				//	<li><a href="{{ route('products.gemstone', $birth_gemstone->slug) }}">{{ $birth_gemstone->getTranslation('name') }}</a></li>
                        
                        //@else
        				//	<li><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $birth_gemstone->getTranslation('name') }}</a></li>
                        //@endif ?>
                <?php // } ?>
          <!-- </ul>  -->
			<?php //} ?>
		  <!-- </li> -->
		<?php // } ?>
                
                                      
        <!-- </ul>     
        </li>  -->
	  <?php // } ?>
		
      <li class="drop-down"><a href="{{route('pages.contact-us')}}">Contact Us</a></li>
	</ul>
   </div>
   


<script>
/*$(function () {
 $('.toggle-menu').click(function(){
  $('.exo-menu').toggleClass('display');
  
 });
 
});*/
</script>
				</div>
			<div class="col-auto col-xl-3  float-right search_area">
			<div class="d-lg-none ml-auto mr-0">
                    <a class="p-2 d-block text-reset" href="javascript:void(0);" data-toggle="class-toggle" data-target=".front-header-search">
                        <i class="las la-search la-flip-horizontal la-2x"></i>
                    </a>
                </div>

                <div class="flex-grow-1 front-header-search d-flex align-items-center bg-white">
                    <div class="position-relative flex-grow-1">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group searching_input_area">
                                    <input type="text" class="border-0 border-lg form-control" id="search" name="keyword" @isset($query)
                                        value="{{ $query }}"
                                    @endisset placeholder="{{translate('I am shopping for...')}}" autocomplete="off">
                                    <div class="input-group-append d-none d-lg-block">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="la la-search la-flip-horizontal fs-18"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px">
                            <div class="search-preloader absolute-top-center">
                                <div class="dot-loader"><div></div><div></div><div></div></div>
                            </div>
                            <div class="search-nothing d-none p-3 text-center fs-16">

                            </div>
                            <div id="search-content" class="text-left">

                            </div>
                        </div>
                    </div>
                </div>

				
			
				</div>
				
            </div>
            </div>
        </div>
    @endif
</header>

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
					
					<?php if(!empty($menu_months)){ ?>
						
							<h4 class="row mega-title">Birthstones</h4>
							<ul class="cabeza">
							<?php foreach($menu_months as $menu_month){ ?>
                                @if (Auth::user())
                                <li><a href="{{ route('birthstones.gemstone_month', strtolower($menu_month)) }}">{{ $menu_month .' Birthstone'; }}</a></li>
                                
                                @else
                                <li><a href="javascript:void(0);" onclick="showLoginCartModal()">{{ $menu_month .' Birthstone'; }}</a></li>
                                @endif
                                <?php } ?>
							</ul>
					<?php } ?>
                </div>
			<?php } ?>
			
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
