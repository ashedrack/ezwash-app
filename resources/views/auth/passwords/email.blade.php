@extends('layouts.auth_layout')

@section('content')
    <div class="card-content">
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('password.email') }}" method="post">
                @csrf
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="email" class="form-control form-control-lg input-lg" id="user-email"
                           placeholder="Your Email Address" name="email" required>
                    <div class="form-control-position">
                        <i class="ft-mail"></i>
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-outline-info btn-lg btn-block"><i class="ft-unlock"></i> Recover Password</button>
            </form>
        </div>
    </div>
    <div class="card-footer border-0">
        <p class="float-sm-right text-center"><a href="{{ route('login') }}" class="card-link">Login</a></p>
    </div>

@endsection

@section('instruction', 'We will send you a link to reset password.')
