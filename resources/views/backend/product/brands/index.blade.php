@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
			<h1 class="h3">{{translate('All Gemstones')}}</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-7">
		<div class="card">
		    <div class="card-header row gutters-5">
				<div class="col text-center text-md-left">
					<h5 class="mb-md-0 h6">{{ translate('Gemstones') }}</h5>
				</div>
				<div class="col-md-4">
					<form class="" id="sort_brands" action="" method="GET">
						<div class="input-group input-group-sm">
					  		<input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
						</div>
					</form>
				</div>
		    </div>
		    <div class="card-body">
		        <table class="table aiz-table mb-0">
		            <thead>
		                <tr>
		                    <th>#</th>
		                    <th>{{translate('Name')}}</th>
		                    <th>{{translate('Logo')}}</th>
		                    <th>{{translate('Birthstone Month')}}</th>
		                    <th>{{translate('Active')}}</th>
		                    <th class="text-right">{{translate('Options')}}</th>
		                </tr>
		            </thead>
		            <tbody>
		                @foreach($brands as $key => $brand)
		                    <tr>
		                        <td>{{ ($key+1) + ($brands->currentPage() - 1)*$brands->perPage() }}</td>
		                        <td>{{ $brand->getTranslation('name') }}</td>
								<td>
		                            <img src="{{ uploaded_asset($brand->logo) }}" alt="" class="h-50px">
		                        </td> 
								<td class="text-center">{{ (!empty($brand->gemstone_month)) ? $brand->gemstone_month : '-'; }}</td>
		                        <td>
									<label class="aiz-switch aiz-switch-success mb-0">
                                			<input onchange="gemstone_is_active(this)" value="1" gemstone_id="{{$brand->id}}" <?php if ($brand->active == 1) echo "checked"; ?> type="checkbox"  >
                                			<span class="slider round"></span>
                            		</label>
								</td>
								<td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('brands.edit', ['id'=>$brand->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ translate('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('brands.destroy', $brand->id)}}" title="{{ translate('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td>
		                    </tr>
		                @endforeach
		            </tbody>
		        </table>
		        <div class="aiz-pagination">
                	{{ $brands->appends(request()->input())->links() }}
            	</div>
		    </div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0 h6">{{ translate('Add New Gemstone') }}</h5>
			</div>
			<div class="card-body">
				<form action="{{ route('brands.store') }}" method="POST">
					@csrf
					<div class="form-group mb-3">
						<label for="name">{{translate('Name')}}</label>
						<input type="text" placeholder="{{translate('Name')}}" name="name" class="form-control" required>
					</div>
					<div class="form-group  mb-3" id="">
                            <label >{{translate('Gemstone Month')}}</label>
                            <select class="form-control aiz-selectpicker" name="gemstone_month" id="gemstone_month"  data-live-search="true">
									 <option value="">{{ translate('Select Month') }}</option>
                                    @foreach ($months as $month)
                                    <option value="{{ $month }}" >{{ $month }}</option>
                                    
                                    @endforeach
                                </select>
                        </div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Logo')}} <small>({{ translate('120x80') }})</small></label>
						<div class="input-group" data-toggle="aizuploader" data-type="image">
							<div class="input-group-prepend">
									<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
							</div>
							<div class="form-control file-amount">{{ translate('Choose File') }}</div>
							<input type="hidden" name="logo" class="selected-files">
						</div>
						<div class="file-preview box sm">
						</div>
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Meta Title')}}</label>
						<input type="text" class="form-control" name="meta_title" placeholder="{{translate('Meta Title')}}">
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Meta Description')}}</label>
						<textarea name="meta_description" rows="5" class="form-control"></textarea>
					</div>
					<div class="form-group mb-3 text-right">
						<button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
    function sort_brands(el){
        $('#sort_brands').submit();
    }
	
	function gemstone_is_active(el){
	    if(el.checked){
	        var status = 1;
	    }
	    else{
	        var status = 0;
	    }
		var gemstone_id= $(el).attr('gemstone_id');
	    $.post('{{ route('brands.gemstone-is-active') }}', {_token:'{{ csrf_token() }}', id:gemstone_id, status:status, gemstone_id:gemstone_id }, function(data){
	        if(data == 1){
	            AIZ.plugins.notify('success', '{{ translate('Gemstone updated successfully') }}');
	        }
	        else{
	            AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
	        }
	    });
	}
</script>
@endsection
