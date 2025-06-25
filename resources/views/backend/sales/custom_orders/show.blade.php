@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">{{ translate('Custom Order Details') }}</h1>
    </div>
    <div class="card-body">
        <div class="row gutters-5">
            <div class="col-md-6">
                <h5>{{ translate('Order Information') }}</h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Description') }}</td>
                            <td>{{ $order->description }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Gemstone') }}</td>
                            <td>{{ $order->gemstone }}</td>
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
                            <td class="text-main text-bold">{{ translate('User Type ') }}</td>
                            <td>{{ $order->user->user_type ?? translate('N/A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h5>{{ translate('Custom Image') }}</h5>
                @if ($order->custom_img)
                    <img src="{{ uploaded_asset($order->custom_img) }}" alt="Custom Image" class="img-fluid border" style="max-height: 300px;">
                @else
                    <p class="text-muted">{{ translate('No image available') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
