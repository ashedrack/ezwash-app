@extends('layouts.app')

@section('title', 'Edit Customer')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Edit Customer</h4>

                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert bg-danger">
                                    <ul class="display-inline-block">
                                        @foreach ($errors->all() as $error)
                                            <li class="text-white">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form class="form" action="{{route('customer.update', ['customer' => $customer->id])}}" method="post" id="addCustomer">
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        @if($authUser->can('list_locations'))
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="location">Location</label>
                                                    <select name="location" id="location" class="form-control" required>
                                                        <option value="">Select a location</option>
                                                        @foreach($locations as $location)
                                                            @if(old('location', $customer->location_id) == $location->id)
                                                                <option value="{{ $location->id }}" selected>{{ $location->name }}</option>
                                                            @else
                                                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput2">Full Name</label>
                                                <input type="text" required name="name" id="name" class="form-control" value="{{ old('name', $customer->name) }}" placeholder="Enter full name">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" id="email" class="form-control" value="{{ $customer->email }}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select name="gender" required id="gender" class="form-control">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ (old('gender', $customer->gender) == 'male')? 'selected': '' }}>Male</option>
                                                    <option value="female" {{ (old('gender', $customer->gender) == 'female')? 'selected': '' }}>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="phone">Contact Phone</label>
                                                <input type="text" name="phone" required id="phone" class="form-control" value="{{ old('phone', $customer->phone) }}"
                                                       minlength="11" maxlength="14" data-rule-validphone
                                                       onkeypress="return allowOnlyPhoneCharacters(event);"
                                                       placeholder="Phone Number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary col-md-2">
                                        <i class="la la-check-square-o"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('more-scripts')
  <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
  <script src="{{asset('js/pages/add-customer.js')}}" type="text/javascript"></script>
@endsection
