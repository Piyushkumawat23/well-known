@extends('backend.layouts.app')

<style>
    .barcode-tag {
        width: 400px;
        height: 80px;
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
        height: 48px;
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
        {{-- <button class="btn btn-primary btn-sm" onclick="window.print()">Print Tags</button> --}}

        <button class="btn btn-primary btn-sm" onclick="downloadPDF()">Print Tags</button>

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
                            <span><strong>{{ get_setting('website_name')}}</strong> </span>
                        </div>
                        <div class="tag-row">
                            <span>{{ '$'.$product->unit_price ?? 'N/A' }}</span>
                        </div>

                        <div class="tag-row">
                            <span>{{ $product->brand->name ?? 'N/A' }}</span>
                        </div>
                        <div class="tag-row">
                            <span> {{ $stock->metal ?? 'N/A' }}</span>
                        </div>
                        <div class="tag-row">
                            <span>{{ $stock->sku ?? 'N/A' }}</span>
                            <span>{{ $stock->size ?? ''}}</span>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    function downloadPDF() {
        const element = document.querySelector('.card-body');
    
        const productName = @json($product->name);
        const dateStr = new Date().toISOString().slice(0,10); // YYYY-MM-DD
        const fileName = `Barcodes-${productName}-${dateStr}.pdf`;
    
        var opt = {
            margin:       0.2,
            filename:     fileName,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
    
        html2pdf().set(opt).from(element).save();
    }
    </script>


@endsection
