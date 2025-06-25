@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">

        <h1 class="h2 fs-16 mb-0">{{ translate('Sample Orders Details') }}</h1>
        <div class="dropdown mb-2 mb-md-0">
            <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                {{translate('Action')}}
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
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    
   
    </div>
    <div class="card-body">
        <div class="row gutters-5">
            <div class="col-md-6">
                <h5>{{ translate('Order Information') }}</h5>
                <table class="table table-bordered">
                    <tbody>
                       
                        <tr>
                            <td class="text-main text-bold">{{ translate('Gemstone') }}</td>
                            <td>{{ $order->gemstone }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Sample Product Name') }}</td>
                            <td>
                                @if ($order->product) 
                                    {{ $order->product->name }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Quantity') }}</td>
                            <td>{{ $order->quantity }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Metal') }}</td>
                            <td>{{ $order->metal }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('User') }}</td>
                            <td>{{ $order->user->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Date & Time') }}</td>
                            <td>{{ $order->created_at ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('status') }}</td>
                            <td>{{ $order->status?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('User Type ') }}</td>
                            <td>{{ $order->user->user_type ?? translate('N/A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
    
            <div class="col-md-6">
                <h5>{{ translate('Sample Product Image') }}</h5>
                @if ($order->product && $order->product->thumbnail_img) 
                    <!-- Assuming 'image' column stores the path of the product image -->
                    <img src="{{ uploaded_asset($order->product->thumbnail_img) }}" alt="Product Image" class="img-fluid border" style="max-height: 300px;">
                @else
                    <p class="text-muted">{{ translate('No image available') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>

function change_status() {
    // Get the selected status and order id
    var status = document.getElementById('update_delivery_status').value;
    var order_id = {{ $order->id }};  // Assuming the order ID is passed to the view
    
    // Send AJAX request to update the order status
    $.ajax({
        url: '{{ route('update-order-status') }}', // Using named route for better readability
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}', // CSRF token for security
            order_id: order_id,  // Single order ID
            status: status
        },
        success: function(response) {
            if (response.success) {
                // Update the status in the view
                alert('Order status updated successfully!');
                location.reload();  // Optionally reload the page to show updated status
            } else {
                alert('Failed to update the order status');
            }
        }
    });
}

    </script>
    
@endsection
