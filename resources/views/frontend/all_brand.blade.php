@extends('frontend.layouts.app')

@section('content')



<section class="pt-8 mb-4 breadcrumb_area" style="background:url({{ static_asset('assets/img/breadcrumb_img.jpg') }})">

    <div class="container">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('All Gemstones') }}</h1>
				<ul class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('brands.all') }}">{{ translate('All Gemstones') }}</a>
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                
            </div>
        </div>
    </div>
</section>


<section class="mb-4 gemstones-list">
    <div class="container">
        <div class="bg-white shadow-sm rounded px-3 pt-3">
            <div class="row row-cols-xxl-4 row-cols-xl-4 row-cols-lg-4 row-cols-md-3 row-cols-2 gutters-10">
                @foreach (\App\Models\Brand::all() as $brand)
                   <!-- <div class="col text-center">
                        <a href="{{ route('products.gemstone', $brand->slug) }}" class="d-block p-1 mb-3 border border-light rounded hov-shadow-md">
                            <img src="{{ uploaded_asset($brand->logo) }}" class="lazyload mx-auto h-300px  mw-100" alt="{{ $brand->getTranslation('name') }}">
                        </a>
                    </div> -->
					
					<div class="content">
						<a href="{{ route('products.gemstone', $brand->slug) }}" target="_blank">
						  <div class="content-overlay"></div>
						  <img class="content-image lazyload mx-auto h-300px  mw-100" src="{{ uploaded_asset($brand->logo) }}">
						  <div class="content-details fadeIn-top fadeIn-right">
							<h3>{{ $brand->getTranslation('name') }} </h3>
							
							@if(!empty($brand->gemstone_month))
							<h6>{{ $brand->gemstone_month }} Birthstone</h6>
							@endif
							<p>{{ $brand->meta_description }}</p>
							
						  </div>
						</a>
					  </div>
					  
                @endforeach
            </div>
        </div>
    </div>
</section>

@endsection
