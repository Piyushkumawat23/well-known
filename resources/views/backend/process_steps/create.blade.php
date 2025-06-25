@extends('backend.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ translate('Add Process Step') }}</h1>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('process_steps.store') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="title">{{ translate('Title') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="description">{{ translate('Description') }}</label>
                            <div class="col-sm-9">
                                <textarea name="description" class="form-control" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ translate('Image') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Upload') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="data[0][image]" id="image_0" class="selected-files">
                                </div>
                                <div class="file-preview box sm" style="margin-top: 10px;">
                                    <img id="image_preview_0" src="#" alt="{{ translate('Image Preview') }}" style="display:none; width: 50px; height: 50px; margin-top: 10px;"/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">{{ translate('Submit') }}</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Function to update image preview
        function updateImagePreview(previewId, imageUrl) {
            if (imageUrl) {
                $(previewId).attr('src', imageUrl).show();
            }
        }

        // AIZ Uploader handler
        $(document).on('click', '[data-toggle="aizuploader"]', function() {
            let input = $(this).find('.selected-files');
            AIZ.plugins.aizUploader({
                multiple: false,
                onSelect: function(files) {
                    let fileUrl = files[0].path;
                    input.val(fileUrl);
                    let previewId = '#image_preview_' + input.attr('id').match(/\d+/)[0];
                    updateImagePreview(previewId, fileUrl);
                }
            });
        });

        // Initial image preview setup for the first row
        updateImagePreview('#image_preview_0', $('#image_0').val());
    });
</script>
@yield('script')
@endsection

