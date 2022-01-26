@extends('layouts.auth_layout')

@section('content')
    <div class="card-body">
        <form class="form-horizontal" action="{{ route('customer_account.complete_setup') }}" method="post">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <fieldset class="form-group position-relative has-icon-left">
                <input type="password" name="password" class="form-control" id="user-password" placeholder="Enter Password"
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
            <button type="submit" class="btn btn-outline-info btn-block"><i class="ft-unlock"></i> Setup Password </button>
        </form>
    </div>
    <div class="card-footer border-0">
        <p class="float-sm-right text-center"><a href="{{ route('login') }}" class="card-link">Login</a></p>
    </div>
@endsection

@section('instruction', '')
