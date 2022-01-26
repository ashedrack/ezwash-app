@extends('layouts.auth_layout')

@section('content')
    <div class="card-body">
        <form class="form-horizontal" action="{{url('/login')}}" method="post" id="loginForm" novalidate>
            @csrf
            <fieldset class="form-group position-relative has-icon-left">
                <input type="text" name="email" class="form-control" id="user-name" value="{{ old('email') }}" placeholder="Your Email" maxlength="100" required>
                <div class="form-control-position">
                    <i class="ft-user"></i>
                </div>
                <div class="invalid-feedback">
                    Please enter your email address.
                </div>
            </fieldset>
            <fieldset class="form-group position-relative has-icon-left">
                <input type="password" name="password" class="form-control" id="user-password" placeholder="Enter Password" maxlength="100" required>
                <div class="form-control-position">
                    <i class="la la-key"></i>
                </div>
            </fieldset>

            <div class="form-group row">
                <div class="col-md-6 col-12 text-center text-sm-left">
                    <fieldset>
                        <input type="checkbox" @if(old('remember') === 'on') checked @endif name="remember" id="remember-me" class="chk-remember">
                        <label for="remember-me"> Remember Me</label>
                    </fieldset>
                </div>
                <div class="col-md-6 col-12 float-sm-left text-center text-sm-right"><a href="{{ route('password.request') }}" class="card-link">Forgot Password?</a></div>
            </div>
            <div class="form-group row">
                <div class="g-recaptcha m-auto" data-sitekey="{{ $recaptcha_site_key }}"></div>
            </div>
            <button type="submit" class="btn btn-outline-info btn-block"><i class="ft-unlock"></i> Login</button>
        </form>
    </div>
@endsection

@section('more-scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection

