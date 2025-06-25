@extends('frontend.layouts.app')

@section('content')

 
<section class="pt-8 mb-4 breadcrumb_area" style="background:#000">

    <div class="container">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">

                <h1 class="fw-600 h4" style="margin-top: 1.8%;">{{ (isset($slug) && !empty($slug)) ? $slug.' - '. translate('Birthstone') : translate('All Birthstones') }}</h1>
				<ul class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('birthstones.all') }}">{{ translate('All Birthstones') }}</a>
                    </li>
					<?php if(isset($slug) && !empty($slug)){ ?>
						<li class="text-dark fw-600 breadcrumb-item">
                        {{ $slug.' - '. translate('Birthstone') }}
                    </li>
					<?php } ?>
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
                @foreach ($gemstones as $brand)
                   
					<div class="content">
						<a href="{{ route('products.gemstone', $brand->slug) }}" target="_blank">
						  <div class="content-overlay"></div>
						  <img class="content-image lazyload mx-auto h-300px  mw-100" src="{{ uploaded_asset($brand->logo) }}">
						  <div class="content-details fadeIn-top fadeIn-right">
							<h3>{{ $brand->getTranslation('name') }}</h3>
							<h6>{{ $brand->gemstone_month }} Birthstone</h6>
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
