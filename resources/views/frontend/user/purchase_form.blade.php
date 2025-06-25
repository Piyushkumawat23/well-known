@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Customer Purchase Form') }}</h5>
        </div>
        <div class="card-body">
            <form class="" action="{{ route('salesperson_customer.customer-purchase-store') }}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="row">
                  <div class="col-md-2">
                      <label>{{ translate('Customer')}}</label>
                  </div>
                  <div class="col-md-10">
                    <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="customer_id" required>
                        <option>Select Customer</option>
                        @foreach($customerlist as $key => $value)
                            <option value="{{$value->id}}">{{ucfirst($value->name).' ('.$value->email.')'}}</option>
                        @endforeach
                    </select>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-2">
                      <label>{{ translate('Category')}}</label>
                  </div>
                  <div class="col-md-10">
                        <select class="form-control mb-3 aiz-selectpicker" name="category_id" id="category_id" data-live-search="true" required>
                            <option value="">{{ translate('Select Category') }}</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                            @foreach ($category->childrenCategories as $childCategory)
                            @include('categories.child_category', ['child_category' => $childCategory])
                            @endforeach
                            @endforeach
                        </select>
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-2">
                      <label>{{ translate('Product')}}</label>
                  </div>
                  <div class="col-md-10">
                        <select class="form-control mb-3 aiz-selectpicker" name="product_id" id="product_id" data-live-search="true" required>
                        </select>
                  </div>
              </div>
              <div class="form-group row">
                  <label class="col-md-2 col-form-label">{{ translate('Quantity') }}</label>
                  <div class="col-md-10">
                      <input class="form-control mb-3" type="number" name="qty" required>
                  </div>
              </div>
              <div class="form-group row">
                  <label class="col-md-2 col-form-label">{{ translate('Price') }}</label>
                  <div class="col-md-10">
                      <input id="price" class="form-control mb-3" type="number" name="price" value="" required disabled>
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-2">
                      <label>{{ translate('Description')}}</label>
                  </div>
                  <div class="col-md-10">
                      <textarea type="text" class="form-control mb-3" rows="3" name="description" data-buttons="bold,underline,italic,|,ul,ol,|,paragraph,|,undo,redo"></textarea>
                  </div>
              </div>              
              <div class="text-right mt-4">
                  <button type="submit" class="btn btn-primary">{{ translate('Submit')}}</button>
              </div>
            </form>            
          
        </div>
    </div>
  
@endsection


@section('script')

<script type="text/javascript">

    $(document).on('change', '[name=category_id]', function() {
        $('#price').val(0);
        var category_id = $(this).val();
        get_all_product(category_id);
    });

    $(document).on('change', '[name=product_id]', function() {
        var price = $(this).find(':selected').attr('price');
        
        $('#price').val(price);
    });

 

    function get_all_product(category_id) {
        $('#product_id').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('salesperson_customer.get-all-product')}}",
            type: 'POST',
            data: {
                category_id  : category_id
            },
            success: function (response) {
                var obj = JSON.parse(response);
                if(obj != '') {
                    $('#product_id').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

</script>
@endsection

