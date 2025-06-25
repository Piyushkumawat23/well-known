@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Orders') }}</h5>
            </div>
            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                        <i class="las la-sync-alt"></i>
                        {{translate('Change Order Status')}}
                    </a>
                </div>
            </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{translate('Choose an order status')}}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <select class="form-control aiz-selectpicker" onchange="change_status()" data-minimum-results-for-search="Infinity" id="update_delivery_status">
                                <option value="pending">{{translate('Pending')}}</option>
                                <option value="confirmed">{{translate('Confirmed')}}</option>
                                <option value="picked_up">{{translate('Picked Up')}}</option>
                                <option value="on_the_way">{{translate('On The Way')}}</option>
                                <option value="delivered">{{translate('Delivered')}}</option>
                                <option value="cancelled">{{translate('Cancel')}}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="change_status()">Save changes</button>

                        </div>
                    </div>
                </div>
            </div>
        
                <div class="col-lg-2 ml-auto">
                    <select class="form-control aiz-selectpicker" name="status" id="status">
                        <option value="">{{translate('Filter by Delivery Status')}}</option>
                        <option value="pending" >{{translate('Pending')}}</option>
                        <option value="confirmed" >{{translate('Confirmed')}}</option>
                        <option value="cancelled" >{{translate('Cancel')}}</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    {{-- <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control" value="{{ $created_at }}" name="created_at" placeholder="{{ translate('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div> --}}
                </div>
                {{-- <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & hit Enter') }}">
                    </div>
                </div> --}}
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                    </div>
                </div>
            </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        
                        <th>{{ translate('#') }}</th>
                        <th data-breakpoints="md">{{ translate(' Customer ') }}</th>
                        <th data-breakpoints="md">{{ translate(' Product Name ') }}</th>
                        <th data-breakpoints="md">{{ translate(' Products Quantity ') }}</th>
                        <th data-breakpoints="md">{{ translate(' Gemstone ') }}</th>
                        <th data-breakpoints="md">{{ translate(' Size ') }}</th>
                        <th data-breakpoints="md">{{ translate(' Date') }}</th>
                        <th data-breakpoints="md">{{ translate(' Status ') }}</th>
                        <th class="text-right" width="15%">{{translate(' Options ')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $key => $order)

                    <tr>

                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$order->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                        </td>

                         {{-- <td>
                            {{ $order->id }}
                        </td> --}}

                        <td>
                            @if ($order->user != null)
                            {{ $order->user->name }}
                            @endif
                        </td>

                        
                        <td>
                            @if ($order->product) 
                                {{ $order->product->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        
                        <td>
                            {{ $order->quantity }}
                        </td>
                        
                        <td>
                            {{ $order->gemstone }}
                        </td>
                        <td>
                            {{ $order->size }}
                        </td>
                        <td>
                            {{ $order->created_at }}
                        </td>
                        <td>
                            {{ $order->status }}
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('sample_orders.show',$order->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                           
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                </table>            

            <div class="aiz-pagination">
                {{ $orders->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        function bulk_delete() {
    var data = new FormData($('#sort_orders')[0]); // Collect form data
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ route('bulk-sample-order-delete') }}",
        type: 'POST',
        data: data,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success) {
                window.location.reload(); // Reload the page
            } else {
                toastr.error(response.message || "Error deleting orders.");
            }
        },
        error: function (xhr) {
            toastr.error("An unexpected error occurred. Please try again.");
        }
    });
}



    </script>
@endsection
