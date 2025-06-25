@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <b class="h4">{{ translate('Sample Orders')}}</b>
            </div>
        </div>
    </div>

    <!-- Sample Orders History -->
    <div class="row gutters-5">
        @if($sampleOrders->isEmpty())
            <div class="col-12">
                <div class="alert alert-info">
                    {{ translate('You have no sample orders yet.') }}
                </div>
            </div>
        @else
            @foreach($sampleOrders as $order)
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>{{ translate('Order #') }} {{ $order->order_id }}</h5>
                                    <small>{{ translate('Ordered on') }}: {{ \Carbon\Carbon::parse($order->created_at)->format('d M, Y') }}</small>
                                </div>
                                <div class="col-md-6 text-right">
                                    @if ($order->status == 'completed')
                                        <span class="badge badge-success">{{ translate('Completed') }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ translate('Pending') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                             

                                <div class="col-md-3">
                                  
                                    @if ($order->product && $order->product->thumbnail_img) 
                                        <!-- Assuming 'image' column stores the path of the product image -->
                                        <img src="{{ uploaded_asset($order->product->thumbnail_img) }}" alt="Product Image" class="img-fluid border" style="max-height: 100px;">
                                    @else
                                        <p class="text-muted">{{ translate('No image available') }}</p>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <p><strong>{{ translate('Product Size:') }}</strong> {{ $order->size }}</p>
                                    <p><strong>{{ translate('Gemstone:') }}</strong> {{ $order->gemstone }}</p>
                                    <p><strong>{{ translate('Metal:') }}</strong> {{ $order->metal }}</p>
                                    {{-- <p><strong>{{ translate('Description:') }}</strong> {{ $order->description }}</p> --}}
                                </div>
                            </div>
                        </div>
                        {{-- <div class="card-footer text-right">
                            <a href="{{ route('sample_orders.view', $order->id) }}" class="btn btn-sm btn-primary">{{ translate('View Details') }}</a>
                        </div> --}}
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Pagination for the orders -->
    <div class="aiz-pagination">
        {{ $sampleOrders->links() }}
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
    </script>
@endsection
