@extends('layouts.auth_layout')

@section('content')

    <div class="card-body">
        <h2  class="guest_notice_salutation">{{ $salutation ?? 'Hi,' }},</h2>
        <br>
        @foreach($messageLines as $message)
        <p class="guest_notice_message">{!! $message !!}</p>
        @endforeach
        <h5>Regards,</h5>
    </div>

@endsection

@section('instruction', '')
