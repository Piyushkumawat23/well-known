@extends('backend.layouts.app')

@section('content')


@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {!! session('success') !!}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {!! session('error') !!}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif


<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('User Bulk Upload')}}</h5>
    </div>
    <div class="card-body">
        <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
            <strong>{{ translate('Step 1')}}:</strong>
            <p>1. {{translate('Download the skeleton file and fill it with proper user data')}}.</p>
            <p>2. {{translate('You can download the example file to understand how the user data must be filled')}}.</p>
            <p>3. {{translate('Once you have downloaded and filled the skeleton file, upload it in the form below and submit')}}.</p>
            <p>4. {{translate('After uploading users you may need to edit them to set profile images or additional preferences')}}.</p>
        </div>
        <br>
        <div class="">
            <a href="{{ static_asset('download/user_bulk_demo.xlsx') }}" download>
                <button class="btn btn-info">{{ translate('Download User xlsx Example') }}</button>
            </a>
        </div>
    </div>
</div>


    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6"><strong>{{translate('Upload User File')}}</strong></h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('bulk_users_upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <div class="col-sm-9">
                        <div class="custom-file">
    						<label class="custom-file-label">
    							<input type="file" name="bulk_file" class="custom-file-input" required>
    							<span class="custom-file-name">{{ translate('Choose File')}}</span>
    						</label>
    					</div>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-info">{{translate('Upload CSV')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection
