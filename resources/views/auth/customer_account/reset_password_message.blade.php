@extends('layouts.auth_layout')

@section('content')

    <div class="card-body">
        <h5>Hi {{$user_name}},</h5>
        <br>
        <h5>You have successfully reset your password, Please head-in to the mobile app to login.</h5>
        <h5>Regards,</h5>
    </div>

@endsection

@section('instruction', '')
