@extends('frontend.layouts.catalog_app')

@section('content')

<style>
    body {
        font-family: Arial, sans-serif;
    }

    /* Responsive Grid */
    .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin: 10px;
    }

    .product {
        padding: 15px;
        text-align: center;
        background: white;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        transition: transform 0.2s ease-in-out;
    }

    .product:hover {
        transform: scale(1.05);
    }

    .product p strong {
    font-weight: bold;
    }

    .product img {
        width: 100%;
        max-width: 200px;
        height: auto;
        object-fit: contain;
    }

    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .filter-container select, .filter-container button {
        padding: 10px 15px;
        font-size: 16px;
        /* border-radius: 8px; */
        border: 1px solid #ccc;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .filter-container button {
        background-color: black;
        color: white;
        border: none;
        cursor: pointer;
    }

    .filter-container button:hover {
        background-color: black;
    }

    .print-container {
        display: flex;
        justify-content: flex-end;
        margin: 10px;
    }

    .print-btn {
        background-color: black;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        /* border-radius: 8px; */
        transition: all 0.3s ease;
    }

    .print-btn:hover {
        background-color: black;
    }

    /* Responsive Adjustments */
    @media (max-width: 1024px) {
        .grid-container {
            grid-template-columns: repeat(2, 1fr); /* 2 columns on medium screens */
        }
    }
    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: repeat(1, 1fr);
        }

        .filter-container {
            flex-direction: column;
            align-items: stretch;
        }

        .print-container {
            justify-content: center;
        }
    }



    @media print {
   

    .grid-container {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(150px, 1fr)) !important;
        gap: 10px !important;
        /* page-break-inside: avoid; */
    }

    .product {
        padding: 10px !important;
        text-align: center !important;
        background: white !important;
        border: 1px solid black !important;
        box-shadow: none !important;
        break-inside: avoid;
    }

   
}

</style>
<style>
    .header-container {
        width: 100%;
        text-align: center;
        font-family: Arial, sans-serif;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* padding: 10px; */
    }
    .logo {
        display: flex;
        align-items: center; 
        min-height: 100px; 
        font-size: 18px;
        font-weight: bold;
    }

    .logo img {
    width: 150px; /* Ya jitna required ho */
    height: 88px; /* Fixed height */
    object-fit: contain;
    display: block;
    }

    .collection-name {
        font-size: 16px;
        font-weight: bold;
    }
    .collection-name p{
        padding-left: 10px;
    }
    .contact-info p {
        margin: 0;
        font-size: 14px;
    }
    hr {
        border: 1px solid #ccc;
    }
</style>
<!-- Filter Section -->


<!-- Print Button -->
<div class="header-container">
    <div class="header-top">
        
        
        <div class="collection-name">
            <p>Welcome to {{ get_setting('site_name') }}</p>
                 2025 Spring Collection </div>


                 <div class="logo"><a class="d-block py-15px mr-3 ml-0" href="{{ route('home') }}">
                    @php
                        $header_logo = get_setting('header_logo');
                    @endphp
                    @if($header_logo != null)
                        <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-50px h-md-50px" height="50">
                    @else
                        <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-50px h-md-50px" height="50">
                    @endif
                </a></div>
        <div class="contact-info">
            @if (get_setting('contact_phone'))
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="tel:{{ get_setting('helpline_number') }}" class="text-reset d-inline-block opacity-60 py-2">
                                <i class="la la-phone"></i>
                                <span>{{ translate('Contact Phone :')}}</span>  
                                <span>{{ get_setting('contact_phone') }}</span>    
                            </a>
                        </li>
                    @endif 
           
                <p><a href="mailto:{{ get_setting('contact_email') }}">{{ get_setting('contact_email') }}</a></p>
        </div>
    </div>
    <hr>
</div>



<div class="filter-container">
    <form method="GET" action="{{ url('/catalog') }}">
        <label for="category">Filter by Category:</label>
        <select name="category_id" id="category">
            <option value="">Select Category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <button type="submit">Apply Filter</button>
    </form>

    <div class="print-container">
        <button class="print-btn" onclick="printProducts()">🖨️ Print Products</button>
    </div>

</div>
<!-- Product Grid -->
<div class="grid-container" id="print-section">
    @foreach ($all_products as $product)
    <div class="product">
        <a href="{{ route('product', $product->slug) }}" target="_blank">
            <img src="{{ uploaded_asset($product->thumbnail_img) }}" 
                alt="{{ $product->img_alt_text ?? $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ uploaded_asset($product->thumbnail_img) }}';">
            <h6>{{ $product->name }}</h6>
            {{-- <p><strong>Price: {{ $product->unit_price }} </strong></p> --}}
            <p>Price: {{ home_base_price($product) ?? $product->unit_price }}</p>
            <p><strong>SKU:</strong> {{ optional($product->stocks->first())->sku ?? '' }}</p>
        </a>
    </div>
    @endforeach
</div>

<div class="footer">
    <p></p>
</div>

@yield('script')

<script>
    function printProducts() {
        window.print();
    }
</script>

@endsection
