@extends('frontend.layouts.app')

@section('content')
<section class="pt-8 breadcrumb_area" style="background:#000">
    <div class="container">
        <div>
            <div class="col-lg-6 text-center" style="text-align: end !important;">
                <h1 class="fw-600 h4" style="margin-top: 1.8% !important;">{{ translate('Customize Product') }}</h1>
            </div>
        </div>
    </div>
</section>
<!-- Customize Product Modal Start -->

<div>
    <div class="customize-modal-dialog modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
        <div class="modal-content position-relative">
            <form action="{{ route('products.custom-store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body gry-bg px-3 pt-3">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ translate('Gemstones') }}</th>
                                    <th>{{ translate('Quantity') }}</th>
                                    <th>{{ translate('Metal') }}</th>
                                    <th>{{ translate('Customization Detail') }}</th>
                                    <th>{{ translate('Upload Design') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="customization-rows">
                                <tr class="customization-row" data-index="0">
                                    <td>
                                        <select class="form-control form-control-sm" data-live-search="true" name="data[0][gemstone]" id="gemstone_0" required>
                                            <option value="">{{ translate('All Gemstones') }}</option>
                                            @foreach (\App\Models\Brand::all() as $brand)
                                                <option value="{{ $brand->slug }}" @isset($brand_id) @if ($brand_id == $brand->id) selected @endif @endisset>
                                                    {{ $brand->getTranslation('name') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control customize-form" name="data[0][quantity]" id="quantity_0" placeholder="{{ translate('Quantity') }}" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control customize-form" name="data[0][metal]" id="metal_0" placeholder="{{ translate('Metal') }}">
                                    </td>
                                    <td>
                                        <textarea class="form-control customize-form" rows="1" name="data[0][description]" id="description_0" placeholder="{{ translate('Add Detail For Customization') }}" required></textarea>
                                    </td>
                                    <td>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Upload') }}</div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="data[0][image]" id="image_0" class="selected-files">
                                        </div>
                                        <div class="file-preview box sm">
                                            <img id="image_preview_0" src="#" alt="{{ translate('Image Preview') }}" style="display:none; width: 50px; height: 50px; margin-top: 10px;"/>
                                        </div> 
                                    </td> 
                                    <!-- <td>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label for="fileInput_0" class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Upload') }}</label>
                                            </div>
                                            <input type="file" id="fileInput_0" name="data[0][image]" accept="image/*" class="form-control-file d-none">
                                        </div>
                                        <div class="file-preview box sm">
                                            <img id="image_preview_0" src="#" alt="{{ translate('Image Preview') }}" style="display:none; width: 50px; height: 50px; margin-top: 10px;"/>
                                        </div>
                                    </td> -->


                                    <td>
                                        <!-- Hidden remove button for the first row -->
                                        <button type="button" class="btn btn-danger remove-row" title="{{ translate('Remove Row') }}" style="display: none;">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-secondary mb-3" id="add-row">{{ translate('Add Row') }}</button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary fw-600">{{ translate('Send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Customize Product Modal End -->
@endsection
@section('script')
<script>
$(document).ready(function () {
    let rowIndex = 1;

    // Function to update image preview
    function updateImagePreview(previewId, file) {
        let previewImage = $(previewId);
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                previewImage.attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.hide().attr('src', '#');
        }
    }

    // Add row button click handler
    $('#add-row').click(function () {
        let newRow = $('#customization-rows .customization-row').first().clone();
        newRow.attr('data-index', rowIndex);

        // Update names and IDs for cloned elements
        newRow.find('select, input, textarea').each(function () {
            let name = $(this).attr('name');
            if (name) {
                name = name.replace(/\d+/, rowIndex);
                $(this).attr('name', name);
            }

            let id = $(this).attr('id');
            if (id) {
                id = id.replace(/\d+/, rowIndex);
                $(this).attr('id', id);
            }
        });

        // Reset values for cloned elements
        newRow.find('select').val('');
        newRow.find('input, textarea').val('');
        newRow.find('.remove-row').show(); // Show remove button for new row
        newRow.find('img').attr('id', 'image_preview_' + rowIndex).hide(); // Set new ID and hide image
        newRow
            .find('.selected-files')
            .attr('id', 'fileInput_' + rowIndex)
            .val(''); // Update file input ID and reset value

        $('#customization-rows').append(newRow);
        rowIndex++;
    });

    // Remove row button click handler
    $(document).on('click', '.remove-row', function () {
        $(this).closest('.customization-row').remove();
    });

    // Trigger file input when label is clicked
    $(document).on('click', 'label[for^="fileInput_"]', function () {
        let fileInputId = $(this).attr('for');
        $(`#${fileInputId}`).trigger('click');
    });

    // File input change handler for image preview
    $(document).on('change', 'input[type="file"]', function () {
        let file = this.files[0];
        let previewId = '#image_preview_' + $(this).attr('id').match(/\d+/)[0];
        updateImagePreview(previewId, file);
    });

    // Initial image preview setup for the first row
    let initialFileInput = $('#fileInput_0');
    if (initialFileInput[0]?.files?.length > 0) {
        updateImagePreview('#image_preview_0', initialFileInput[0].files[0]);
    }
});


</script>
@yield('script')
@endsection