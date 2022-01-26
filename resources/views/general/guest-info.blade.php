@extends('layouts.auth_layout')

@section('content')

    <div class="card-body">
        <h5>{{ $salutation }}</h5>
        <br>
        <h5>{!! $responseMessage !!}</h5>
    </div>

@endsection

@section('instruction', '')
