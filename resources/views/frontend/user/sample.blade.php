@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Sample History') }}</h5>
        </div>
        @if ($sampleOrders->count() > 0)
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('image')}}</th>
                            <th>{{ translate('Order Code')}}</th>
                            <th data-breakpoints="md">{{ translate('Order Date')}}</th>
                            {{-- <th>{{ translate('Amount')}}</th> --}}
                            <th data-breakpoints="md">{{ translate('Status')}}</th>
                            <th data-breakpoints="md">{{ translate('size')}}</th>
                            <th data-breakpoints="md">{{ translate('gemstone')}}</th>
                            <th data-breakpoints="md">{{ translate('metal')}}</th>
                            {{-- <th data-breakpoints="md">{{ translate('Payment Status')}}</th> --}}
                            @if(Auth::user()->is_salesperson == 1)
                                <th data-breakpoints="md">{{ translate('Customer')}}</th>
                            @endif
                            <th class="text-right">{{ translate('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sampleOrders as $key => $order)
                            <tr>
                                <td>
                                    @if ($order->product && $order->product->thumbnail_img) 
                                        <a href="{{ route('product', $order->product->slug) }}">
                                            <img src="{{ uploaded_asset($order->product->thumbnail_img) }}" alt="Product Image" class="img-fluid border" style="max-height: 50px;">
                                        </a>
                                    @else
                                        <p class="text-muted">{{ translate('No image available') }}</p>
                                    @endif
                                </td>
                                
                                {{-- <td>
                                    <a href="{{ route('purchase_history.details', encrypt($order->id)) }}">{{ $order->order_id }}</a>
                                </td> --}}
                                <td>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->order_id))) }}
                                </td>
                                <td>{{ date('d-m-Y', strtotime($order->created_at)) }}</td>
                                {{-- <td>
                                    {{ single_price($order->grand_total) }}
                                </td> --}}
                                <td>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->status))) }}
                                    {{-- @if($order->delivery_viewed == 0)
                                        <span class="ml-2" style="color:green"><strong>*</strong></span>
                                    @endif --}}
                                </td>
                                <td>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->size))) }}
                                </td>
                                <td>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->gemstone))) }}
                                </td>
                                <td>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->metal))) }}
                                </td>
                                {{-- <td>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->status))) }}
                                </td> --}}
                                {{-- <td>
                                    @if ($order->payment_status == 'paid')
                                        <span class="badge badge-inline badge-success">{{ translate('Paid') }}</span>
                                    @else
                                        <span class="badge badge-inline badge-danger">{{ translate('Unpaid') }}</span>
                                    @endif
                                    @if($order->payment_status_viewed == 0)
                                        <span class="ml-2" style="color:green"><strong>*</strong></span>
                                    @endif
                                </td> --}}
                                {{-- @if(Auth::user()->is_salesperson == 1)
                                    <td>
                                        @if ($order->salespersonCustomer != null)
                                            {{ $order->salespersonCustomer->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif --}}

                                <td class="text-right">
                                    {{-- Cancel Button for Customers --}}
                                    @if ($order->status != 'cancelled')
                                        <a href="javascript:void(0)" 
                                           class="btn btn-soft-danger btn-icon btn-circle btn-sm" 
                                           onclick="cancelOrder({{ $order->id }})"
                                           title="{{ translate('Cancel') }}">
                                            <i class="las la-times"></i> {{-- Cancel Icon --}}
                                        </a>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{ translate('Cancelled') }}</span>
                                    @endif
                                </td>
                                
                                
                         
            
                             

                                {{-- <td class="text-right">
                                    @if ($order->states != 'cancelled')
                                        <a href="javascript:void(0)" 
                                           class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-cancel" 
                                           data-id="{{ $order->id }}" 
                                           title="{{ translate('Cancel') }}">
                                            <i class="las la-times"></i> 
                                        </a>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{ translate('Cancelled') }}</span>
                                    @endif
                                </td> --}}
                                
                                {{-- <td class="text-right">
                                   @if ($order->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                                        <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('purchase_history.destroy', $order->id) }}" title="{{ translate('Cancel') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    @endif 


                                   
                                    <a href="{{ route('sample_orders.view', encrypt($order->id)) }}" class="btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ translate('Order Details') }}">
                                        <i class="las la-eye"></i>
                                    </a> 
                                     <a class="btn btn-soft-warning btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" title="{{ translate('Download Invoice') }}">
                                        <i class="las la-download"></i>
                                    </a>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $sampleOrders->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection



@section('modal')

<div class="modal fade" id="addToCart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
        <div class="modal-content position-relative">
            <div class="c-preloader">
                <i class="fa fa-spin fa-spinner"></i>
            </div>
            <button type="button" class="close absolute-close-btn" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div id="addToCart-modal-body">

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function removeFromWishlist(id){
            $.post('{{ route('wishlists.remove') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                $('#wishlist_'+id).hide();
                AIZ.plugins.notify('success', '{{ translate('Item has been renoved from wishlist') }}');
            })
        }
   

       
    function cancelOrder(orderId) {
        if (confirm("Are you sure you want to cancel this order?")) {
            // AJAX request using fetch
            fetch(`/well-known/sample-orders/cancel/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);

                    // Update button inline to show 'Cancelled'
                    let parentCell = document.querySelector(`a[onclick="cancelOrder(${orderId})"]`).parentElement;
                    parentCell.innerHTML = `<span class="badge badge-inline badge-secondary">Cancelled</span>`;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong. Please try again later.');
            });
        }
    }
</script>



@endsection
