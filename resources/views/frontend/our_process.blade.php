<!-- resources/views/frontend/process.blade.php -->

@extends('frontend.layouts.app')

@section('content')
    <!--breadcrumbs area start-->
    <div class="breadcrumbs_area">
    </div>
    <!--breadcrumbs area end-->

    <!--services img area-->
    <div class="services_gallery">
        <div class="container">  
            <div class="row">
                @foreach($processSteps as $processStep)
                    <div class="col-lg-4 col-md-6">
                        <div class="single_services">
                            <div class="services_thumb">
                                <img src="{{ uploaded_asset($processStep->image) }}" alt="{{ $processStep->title }}">
                            </div>
                            <div class="services_content">
                                <h3>{{ $processStep->title }}</h3>
                                <p>{{ $processStep->description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>     
    </div>
    <!--services img end-->
@endsection
