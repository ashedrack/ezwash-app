@extends('layouts.auth_layout')

@section('content')

    <div class="card-body">
        <p id="error_message"></p>
        <form class="form-horizontal reset-password-form" action="{{ url('customer_account/reset_password') }}" method="post">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <fieldset class="form-group position-relative has-icon-left">
                <input type="password" name="password" class="form-control user_password" id="user-password user_password" placeholder="Enter Password"
                       required>
                <div class="form-control-position">
                    <i class="la la-key"></i>
                </div>
                <small id="emailHelp" class="form-text text-muted">Password must be at least 8 characters</small>
            </fieldset>
            <fieldset class="form-group position-relative has-icon-left">
                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm Password"
                       required>
                <div class="form-control-position">
                    <i class="la la-key"></i>
                </div>
            </fieldset>
            <button type="submit" class="btn btn-outline-info btn-block"><i class="ft-unlock"></i> Reset Password </button>
        </form>
    </div>

@endsection

@section('instruction', '')
