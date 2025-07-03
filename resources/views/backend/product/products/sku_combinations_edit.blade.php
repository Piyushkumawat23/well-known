@if(count($combinations[0]) > 0)
<table class="table table-bordered aiz-table">
    <thead>
        <tr>
            <td class="text-center">
                {{translate('Variant')}}
            </td>
            <td class="text-center">
                {{translate('Variant Price')}}
            </td>
            <td class="text-center" data-breakpoints="lg">
                {{translate('SKU')}}
            </td>
            <td class="text-center" data-breakpoints="lg">
                {{translate('Quantity')}}
            </td>
            <!-- <td class="text-center" data-breakpoints="lg">
                {{translate('Photo')}}
            </td> -->
        </tr>
    </thead>
    <tbody>

        @foreach ($combinations as $key => $combination)
        @php
        $str = '';
        foreach ($combination as $index => $item){
            if($index > 0) {
                $str .= '-' . str_replace(' ', '', $item);
            } else {
                if($colors_active == 1) {
                    $color_name = \App\Models\Color::where('code', $item)->first()->name;
                    $str .= $color_name;
                } else {
                    $str .= str_replace(' ', '', $item);
                }
            }
        }
    
        $normalized = strtolower(trim($str));
        $field_key = str_replace([' ', '.', '-', '/', '(', ')'], '_', $str);
        $stock = $product->stocks->where('variant', $str)->first();
    @endphp
    
    <tr>
        <input type="hidden" name="variant[]" value="{{ $str }}">
    
        <td>{{ $str }}</td>
        <td><input type="number" name="price_{{ $field_key }}" value="{{ $stock->price ?? $unit_price }}" class="form-control" required></td>
        <td><input type="text" name="sku_{{ $field_key }}" value="{{ $sku_list[$key] ?? ($stock->sku ?? '') }}" class="form-control"></td>
        <td><input type="number" name="qty_{{ $field_key }}" value="{{ $stock->qty ?? 10 }}" class="form-control" required></td>
    </tr>
    
@endforeach


    </tbody>
</table>
@endif
