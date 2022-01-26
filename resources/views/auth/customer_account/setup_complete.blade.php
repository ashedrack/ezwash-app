@extends('layouts.auth_layout')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-header">{{ __('Account Setup Successful') }}</div>

                    <div class="card-body">
                        {{ __('Proceed to the app to login.') }}
                        {{--{{ __('If you did not receive the email') }}--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
