@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{ translate('All Process Steps') }}</h5>
        </div>
        <div class="col text-right">
            <a href="{{ route('process_steps.create') }}" class="btn btn-primary">{{ translate('Add Process Step') }}</a>
        </div>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Description') }}</th>
                    <th>{{ translate('Image') }}</th>
                    <th class="text-right">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($processSteps as $processStep)
                    <tr>
                        <td>{{ $processStep->title }}</td>
                        <td>{{ $processStep->description }}</td>
                        <td>
                            <img src="{{ uploaded_asset($processStep->image) }}" alt="{{ $processStep->title }}" width="100">
                        </td>
                        <td class="text-right">
                            <a href="{{ route('process_steps.edit', $processStep->id) }}" class="btn btn-soft-warning btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <form action="{{ route('process_steps.destroy', $processStep->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="aiz-pagination">
            {{ $processSteps->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("click", ".confirm-delete", function(e) {
            e.preventDefault();
            var form = $(this).closest("form");
            if(confirm("Are you sure you want to delete this item?")) {
                form.submit();
            }
        });
    </script>
    @yield('script')
@endsection
