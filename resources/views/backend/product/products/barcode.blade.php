@extends('backend.layouts.app')

<style>
    .barcode-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5cm;
        padding: 0.5cm;
        box-sizing: border-box;
    }

    .barcode-tag-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .barcode-tag {
        width: 5.5cm;
        height: 1cm;
        border: 1px solid #000;
        display: flex;
        font-family: Arial, sans-serif;
        font-size: 8px;
        box-sizing: border-box;
        page-break-inside: avoid;
    }

    .tag-front {
        width: 2.6cm;
        height: 1cm;
        border-right: 1px dashed #000;
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        padding: 2px;
        box-sizing: border-box;
        line-height: 8px;
    }

    .website-name {
        font-size: 8px;
        font-weight: bold;
        text-align: center;
        width: 100%;
    }

    .brand-name {
        font-size: 8px;
        text-align: center;
        width: 100%;
    }

    .unit-sku-row,
    .metal-size-row {
        display: flex;
        justify-content: space-between;
        width: 100%;
        font-size: 8px;
        font-weight: bold;
    }

    .tag-back {
        width: 2.9cm;
        height: 1cm;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .custom-barcode img {
        width: 2.8cm;
        height: 1.2cm;
        object-fit: contain;
        padding-left: 2px;
    }

    .print-controls {
        margin-top: 4px;
    }

    .print-controls input {
        width: 50px;
        margin-right: 5px;
    }
</style>

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 h6">Barcodes for: {{ $product->name ?? 'N/A' }}</h5>
        <a class="btn btn-soft-primary btn-icon btn-sm" href="{{ route('products.all') }}" title="{{ translate('Back') }}">
            <i class="las la-arrow-left"></i>
        </a>
    </div>

    <div class="card-body text-center">
        @php $barcode = new \Milon\Barcode\DNS1D(); @endphp

        @if(!empty($product->variant_product) && $product->stocks && $product->stocks->count())
        <div class="barcode-container">
            @foreach ($product->stocks as $stock)
                @if (!empty($stock->sku))
                    @php
                        $barcodeData = $barcode->getBarcodePNG($stock->sku, 'C128', 2.2, 40);
                    @endphp

                    <div class="barcode-tag-wrapper">
                        <div class="barcode-tag" id="tag-{{ $stock->sku }}">
                            <div class="tag-front">
                                <div class="website-name">{{ get_setting('website_name') }}</div>
                                <div class="brand-name">{{ optional($product->brand)->name ?? 'N/A' }}</div>
                                <div class="unit-sku-row">
                                    <span>{{ $product->unit_price ? '$' . $product->unit_price : 'N/A' }}</span>
                                    <span>{{ $stock->sku ?? 'N/A' }}</span>
                                </div>
                                <div class="metal-size-row">
                                    <span>{{ $stock->metal ?? 'N/A' }}</span>
                                    <span>{{ $stock->size ?? '' }}</span>
                                </div>
                            </div>
                            <div class="tag-back">
                                <div class="custom-barcode">
                                    <img src="data:image/png;base64,{{ $barcodeData }}" alt="Barcode">
                                </div>
                            </div>
                        </div>

                        <div class="print-controls">
                            <input type="number" min="1" value="1" id="qty-{{ $stock->sku }}" />
                            <button onclick="printTag('{{ $stock->sku }}')" class="btn btn-sm btn-primary">Print</button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        @else
            <p>No variants found.</p>
        @endif
    </div>
</div>
@endsection

<script>
function printTag(sku) {
    const qtyInput = document.getElementById('qty-' + sku);
    const tagElement = document.getElementById('tag-' + sku);
    if (!qtyInput || !tagElement) return;

    const qty = parseInt(qtyInput.value);
    if (!qty || qty < 1) return;

    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.flexWrap = 'wrap';
    container.style.gap = '0.5cm';
    container.style.padding = '0.5cm';

    for (let i = 0; i < qty; i++) {
        const clone = tagElement.cloneNode(true);
        container.appendChild(clone);
    }

    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Barcode</title>
            <style>
                body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                .barcode-tag {
                    width: 5.5cm;
                    height: 1cm;
                    display: flex;
                    font-size: 8px;
                    box-sizing: border-box;
                    margin-bottom: 0.38cm;
                    border: 1px solid #000;
                }
                .tag-front {
                    width: 2.6cm;
                    height: 1cm;
                    border-right: 1px dashed #000;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-evenly;
                    padding: 2px;
                    box-sizing: border-box;
                    line-height: 8px;
                }
                .website-name {
                    font-size: 8px;
                    font-weight: bold;
                    text-align: center;
                }
                .brand-name {
                    font-size: 8px;
                    text-align: center;
                }
                .unit-sku-row,
                .metal-size-row {
                    display: flex;
                    justify-content: space-between;
                    font-size: 8px;
                    font-weight: bold;
                }
                .tag-back {
                    width: 2.9cm;
                    height: 1cm;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .custom-barcode img {
                    width: 2.8cm;
                    height: 1.2cm;
                    object-fit: contain;
                }
            </style>
        </head>
        <body>${container.innerHTML}</body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}
</script>
