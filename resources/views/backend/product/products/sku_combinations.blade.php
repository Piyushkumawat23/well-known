@if(count($combinations[0]) > 0)
<table class="table table-bordered aiz-table">
    <thead>
        <tr>
            <td class="text-center">{{ translate('Variant') }}</td>
            <td class="text-center">{{ translate('Variant Price') }}</td>
            <td class="text-center" data-breakpoints="lg">{{ translate('SKU') }}</td>
            <td class="text-center" data-breakpoints="lg">{{ translate('Quantity') }}</td>
        </tr>
    </thead>
    <tbody>
    @foreach ($combinations as $key => $combination)
        @php
            $str = '';
            foreach ($combination as $index => $item) {
                if ($index > 0) {
                    $str .= '-' . str_replace(' ', '', $item);
                } else {
                    if ($colors_active == 1) {
                        $color_name = \App\Models\Color::where('code', $item)->first()->name;
                        $str .= $color_name;
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
            }
        @endphp

        @if(strlen($str) > 0)
            <tr class="variant">
                <td>
                    <label for="" class="control-label">{{ $str }}</label>
                </td>
                <td>
                    <input type="number" lang="en" name="price_{{ $str }}" value="{{ $unit_price }}" min="0" step="0.01" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="sku_{{ $str }}" value="{{ $sku_list[$key] ?? '' }}" class="form-control">
                </td>
                <td>
                    <input type="number" lang="en" name="qty_{{ $str }}" value="10" min="0" step="1" class="form-control" required>
                </td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>
@endif
