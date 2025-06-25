@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="container">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Add New User') }}</h5>
           <a class="btn btn-soft-primary btn-icon btn-sm" href="{{ route('customers.information', ['id' => $user_id]) }}" title="{{ translate('Back') }}">
                <i class="las la-arrow-left"></i>
            </a>
    
        </div>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="user_id" value="{{ $user_id }}">

        {{-- Company Name --}}
        <div class="form-group">
            <label for="business_name">Company Name:</label>
            <input type="text" id="business_name" name="business_name" class="form-control"
            placeholder="Type company name" value="{{ old('business_name', $business_name) }}">
        </div>
        
        {{-- Product Search --}}

        <div class="form-group">
            <label for="product_search">Search Product:</label>
            <input type="text" id="product_search" class="form-control" placeholder="Type product name">
            <div id="product_list"></div>
        </div>

        {{-- Product List (selected) --}}
        <div id="cart_items"></div>

        {{-- Payment Method --}}
        <div class="form-group mt-3">
            <label for="payment_option">Payment Method</label>
            <select name="payment_option" class="form-control" required>
                <option value="cod">Cash on Delivery</option>
                <option value="bank">Bank Transfer</option>
                <option value="online">Online</option>
            </select>
        </div>

        {{-- Date --}}
        <div class="form-group mt-3">
            <label for="order_date">Order Date</label>
            <input type="date" name="order_date" class="form-control" required>
        </div>



        {{-- Address Selection --}}

        <div class="row gutters-5">
            @foreach ($addresses as $address)
            @php
            $checked = '';
            if ($address->set_default) {
            $checked = 'checked';
            }
            @endphp
            <div class="col-md-6 mb-3">
                <label class="aiz-megabox d-block bg-white mb-0 tax_rate" address_id="{{ $address->id }}"
                    zip_code="{{ $address->postal_code }}">
                    <input type="radio" name="address_id" class="tax_radio" value="{{ $address->id }}" {{ $checked }}
                        required>
                    <span class="d-flex p-3 aiz-megabox-elem">
                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                        <span class="flex-grow-1 pl-3 text-left">
                            <div><span class="opacity-60">Address:</span> <span
                                    class="fw-600 ml-2">{{ $address->address }}</span></div>
                            <div><span class="opacity-60">Zip Code:</span> <span
                                    class="fw-600 ml-2">{{ $address->postal_code }}</span></div>
                            <div><span class="opacity-60">City:</span> <span
                                    class="fw-600 ml-2">{{ optional($address->city)->name }}</span></div>
                            <div><span class="opacity-60">State:</span> <span
                                    class="fw-600 ml-2">{{ optional($address->state)->name }}</span></div>
                            <div><span class="opacity-60">Country:</span> <span
                                    class="fw-600 ml-2">{{ optional($address->country)->name }}</span></div>
                            <div><span class="opacity-60">Phone:</span> <span
                                    class="fw-600 ml-2">{{ $address->phone }}</span></div>
                        </span>
                    </span>
                </label>
            </div>
            @endforeach
 
            {{-- Add New Address --}}
            <div class="col-md-6 mx-auto mb-3">
                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center"
                    onclick="add_new_address({{ $user_id }})">
                    <i class="las la-plus la-2x mb-3"></i>
                    <div class="alpha-7">Add New Address</div>
                </div>
            </div>
 
 
        </div>


        {{-- Submit --}}
        <button type="submit" class="btn btn-primary mt-4">Create Order</button>
    </form>
</div>
</div>





{{-- JS for Search --}}
<script>
document.getElementById('product_search').addEventListener('keyup', function() {
    let query = this.value;

    if (query.length >= 2) {
        fetch('{{ route("products.search") }}?q=' + query)
            .then(response => response.json())
            .then(data => {
                let html = '<ul class="list-group">';
                data.forEach(product => {
                    html += `<li class="list-group-item" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">
                        ${product.name} - $${product.price}
                    </li>`;
                });
                html += '</ul>';
                document.getElementById('product_list').innerHTML = html;
            });
    } else {
        document.getElementById('product_list').innerHTML = '';
    }
});

function addToCart(id, name, price) {
    let cart = document.getElementById('cart_items');
    let html = `
        <div class="card mt-2 p-2">
            <input type="hidden" name="products[]" value="${id}">
            <strong>${name}</strong> - $${price}
            <br>
            Quantity: <input type="number" name="quantities[]" value="1" min="1" style="width: 60px;">
        </div>
    `;
    cart.innerHTML += html;
    document.getElementById('product_list').innerHTML = '';
    document.getElementById('product_search').value = '';
}
</script>

@section('modal')
@include('backend.sales.all_orders.address_modal')
@endsection
@endsection
