<form id="add_Child_product" method="POST">
<div class="card">
    <div class="card-body">    
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Salesperson</label>
            <div class="col-md-8">
                <select class="form-control aiz-selectpicker" onchange="add_salesperson(this)" name="salesperson_id" id="child_product" data-selected-text-format="count" data-live-search="true" data-placeholder="{{ translate('Choose Salesperson') }}" data-mainid="{{$mainid}}">
                        <option value="">-Select Salesperson</option>
                    @foreach($allSalespersonData as $key => $allSalesperson)
                    
                        @if($selected_id == $allSalesperson['id'])
                            <option value="{{$allSalesperson['id']}}" selected>{{$allSalesperson['name']}}</option>
                        @else
                            <option value="{{$allSalesperson['id']}}">{{$allSalesperson['name']}}</option>
                        @endif
                        
                    @endforeach
                </select>
                <input type="hidden" name="mainid" value="{{$mainid}}">
            </div>
        </div>
    </div>
</div>
</form>
 
<script src="{{ static_asset('assets/js/aiz-core.js') }}" ></script>
