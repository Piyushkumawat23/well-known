@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{translate('Wholesale Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.wholesale_update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                                                <button
                            type="button"
                            class="btn btn-soft-secondary btn-sm"
                            data-toggle="add-more"
                            data-content='<div class="form-group row">
                                
                                <div class="col-sm-3">
                                    <label class="col-from-label">{{translate('Min Amount')}}</label>
                                    <input type="text" name="min_amount[]" class="form-control" value="">
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-from-label">{{translate('Max Amount')}}</label>
                                    <input type="text" name="max_amount[]" class="form-control" value="">
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-from-label">{{translate('Discount Amount')}}</label>
                                    <input type="text" name="discount_amount[]" class="form-control" value="">
                                </div>
                                <div class="col-sm-2">
                                    <label class="col-from-label">{{translate('Discount Percentage')}}</label>
                                    <input type="text" name="discount_percentage[]" class="form-control" value="">
                                </div>
                            
                                <div class="col-sm-1">

                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                        <i class="las la-times"></i>
                                    </button>
                                </div>
                            </div>'
                            data-target=".add_more_whole_sale">
                            {{ translate('Add New') }}
                        </button>
                        <div class="add_more_whole_sale">
                            
                             @foreach($wholesaleCommission as $key => $value)
                                 <div class="form-group row">
                                    
                                    <div class="col-sm-3">
                                        <label class="col-from-label">{{translate('Min Amount')}}</label>
                                        <input type="text" name="min_amount[]" class="form-control" value="{{$value->min_amount}}">
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="col-from-label">{{translate('Max Amount')}}</label>
                                        <input type="text" name="max_amount[]" class="form-control" value="{{$value->max_amount}}">
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="col-from-label">{{translate('Discount Amount')}}</label>
                                        <input type="text" name="discount_amount[]" class="form-control" value="{{$value->discount_amount}}">
                                    </div>
                                    <div class="col-sm-2">
                                        <label class="col-from-label">{{translate('Discount Percentage')}}</label>
                                        <input type="text" name="discount_percentage[]" class="form-control" value="{{$value->discount_percentage}}">
                                    </div>
                                
                                    <div class="col-sm-1">

                                        <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                            <i class="las la-times"></i>
                                        </button>
                                    </div>
                                </div>
                             @endforeach
                        </div>
                        <div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
