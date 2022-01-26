@extends('layouts.app')

@section('title', 'Add Location')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Add Location</h4>

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
                            <form class="form" action="{{route('location.save')}}" id="createLocation" method="post", enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        @if(isset($companies) && !empty($companies))
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput2">Company</label>
                                                <select name="company" id="company_select" class="form-control" required>
                                                    <option value="">Select a company</option>
                                                    @foreach($companies as $company)
                                                        @if(!is_null(old('company')) &&  old('company') === $company->id)
                                                            <option value="{{ $company->id }}" selected>{{ $company->name }}</option>
                                                        @else
                                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput2">Location Name</label>
                                                <input type="text" name="name" id="location_name" value="{{ old('name') }}" class="form-control" data-rule-maxlength="100" required placeholder="Name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput3">Contact Phone</label>
                                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control" data-rule-validphone required onkeypress="return allowOnlyPhoneCharacters(event);" placeholder="Phone Number">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput4">Full Address</label>
                                                <input type="text" required name="address" id="address" class="form-control" value="{{ old('address') }}">
                                            </div>
                                        </div>
                                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput4">Number of Lockers</label>
                                                <input type="number" required name="lockers_count" id="no_of_lockers" value="{{ old('lockers_count') }}" class="form-control" placeholder="Number of lockers">
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
    <script src="{{ asset('plugins/lodash.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $placesApiKey }}&libraries=places"></script>
    <script defer src="{{asset('js/pages/add_location.js')}}" type="text/javascript"></script>
@endsection
