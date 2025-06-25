@extends('frontend.layouts.app')

@section('content')
    <section class="gry-bg py-4">
        <div class="profile">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 mx-auto">
                        <div class="card">
                            <div class="text-center pt-4">
                                <h1 class="h4 fw-600">
                                    {{ translate('Get In Touch')}}
                                </h1>
                            </div>
                            <div class="px-4 py-3 py-lg-4">
                                <div class="">
                                    <form id="contact-form" class="form-default" role="form" action="{{route('pages.contact-save')}}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <input type="text" required class="form-control" value="" placeholder="{{  translate('Name') }}" name="name">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" required class="form-control" value="" placeholder="{{  translate('E-mail') }}" name="email">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" required class="form-control" value="" placeholder="{{  translate('Subject') }}" name="subject">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" required class="form-control" value="" placeholder="{{  translate('Phone') }}" name="phone">
                                        </div>
                                        <div class="form-group">
                                            <textarea type="text" required class="form-control" value="" placeholder="{{  translate('Type your message here') }}" name="message" rows="8" cols="8"></textarea>
                                        </div>
                                        <div class="mb-5">
                                            <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Submit') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection