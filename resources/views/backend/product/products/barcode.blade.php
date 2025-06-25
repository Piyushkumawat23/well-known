@extends('backend.layouts.app')

<style>
    .barcode-tag {
        width: 400px;
        height: 60px;
        margin: 8px auto;
        border: 1px solid #000;
        font-size: 10px;
        font-family: Arial, sans-serif;
        line-height: 1.2;
        display: flex;
        flex-direction: row;
        page-break-inside: avoid;
    }

    .tag-front, .tag-back {
        width: 50%;
        height: 100%;
        padding: 6px;
        box-sizing: border-box;
    }

    .tag-front {
        border-right: 1px dashed #000;
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
    }

    .tag-back {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tag-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
    }

    .custom-barcode img {
        width: 190px;
        height: 45px;
        object-fit: contain;
    }

    .action-buttons {
        margin-bottom: 10px;
        text-align: right;
        padding-right: 20px;
    }

    .btn-sm {
        font-size: 10px;
        padding: 2px 6px;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        .barcode-tag, .barcode-tag * {
            visibility: visible !important;
        }

        .barcode-tag {
            page-break-inside: avoid;
        }

        .action-buttons, .card-header, .btn {
            display: none !important;
        }

        .custom-barcode img {
            width: 100% !important;
        }
    }
</style>

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 h6">Barcodes for: {{ $product->name }}</h5>
        <a class="btn btn-soft-primary btn-icon btn-sm" href="{{ route('products.all') }}" title="{{ translate('Back') }}">
            <i class="las la-arrow-left"></i>
        </a>
    </div>

    <div class="action-buttons">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Print Tags</button>
    </div>

    <div class="card-body text-center">
        @php
            $barcode = new \Milon\Barcode\DNS1D();
        @endphp

        @if($product->variant_product && $product->stocks->count())
            @foreach ($product->stocks as $stock)
                <div class="barcode-tag">
                    <div class="tag-front">
                        <div class="tag-row">
                            <span><strong>Brand:</strong> {{ $product->brand->name ?? 'N/A' }}</span>
                        </div>
                        <div class="tag-row">
                            <span><strong>Metal:</strong> {{ $stock->metal ?? 'N/A' }}</span>
                        </div>
                        <div class="tag-row">
                            <span><strong>SKU:</strong> {{ $stock->sku ?? 'N/A' }}</span>
                            <span><strong>Size:</strong> {{ $stock->size ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="tag-back">
                        @if (!empty($stock->sku))
                            <div class="custom-barcode">
                                <img src="data:image/png;base64,{{ $barcode->getBarcodePNG($stock->sku, 'C128') }}" alt="Barcode">
                            </div>
                        @else
                            <span>No SKU</span>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <p>No variants found.</p>
        @endif
    </div>
</div>
@endsection
