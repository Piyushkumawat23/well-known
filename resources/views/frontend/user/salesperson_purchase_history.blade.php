@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Customer Purchase History') }}</h5>
        </div>
        <?php //echo'<pre>'; print_r($orders); die;?>
        @if (count($orders) > 0 )
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th data-breakpoints="md">Date</th>
                            <th>Customer</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th data-breakpoints="md">{{ translate('Status')}}</th>
                            <th class="text-right">{{ translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php //echo'<pre>';print_r($orders);die;?>
                        @foreach ($orders as $key => $order)
                                <tr>
                                   
                                    <td>{{ date('d-m-Y', strtotime($order->created_at)) }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->category_name }}</td>
                                    <td>{{ $order->product_name }}</td>
                                    <td>{{ $order->qty }}</td>
                                    <td>{{ single_price($order->price) }}</td>
                                    <td>
                                        {{ translate(ucfirst(str_replace('_', ' ', $order->status))) }}
                                        
                                    </td>
                                    
                                    <td class="text-right">
                                      
                                        <a href="javascript:void(0);" class="btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ translate('Order Details') }}"  data-toggle="modal" data-target="#order_details_new{{$order->id}}">
                                            <i class="las la-eye"></i>
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="order_details_new{{$order->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">{{ translate('Description') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php echo $order->description;?>
                                            </div>
                                        </div>
                                    </div>

                                </div>                                
                            
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $orders->links() }}
              	</div>
            </div>
        @endif
    </div>
@endsection


