@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Add New User') }}</h5>
        <a class="btn btn-soft-primary btn-icon btn-sm" href="{{route('customers.index')}}" title="{{ translate('Back') }}">
            <i class="las la-arrow-left"></i>
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>{{ translate('Name') }}</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>{{ translate('Email') }}</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>{{ translate('Phone') }}</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="form-group">
                <label>{{ translate('Password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary mt-3">{{ translate('Save') }}</button>
        </form>
    </div>
</div>
@endsection
