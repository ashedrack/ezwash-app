@extends('layouts.app')

@section('title', 'Add Employee')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection
@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title" id="basic-layout-form">Add Employee</h1>

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
                            <form class="form" action="{{route('employee.save')}}" method="post" id="addEmployee">
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        @if($authUser->can('create_company'))
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="company">Company</label>
                                                    <?php
                                                    $oldCompanyInput = old('company');
                                                    ?>
                                                    <select name="company" id="company" class="form-control" data-selected-company="{{ $oldCompanyInput }}">
                                                        <option value="">Select a company</option>
                                                        @foreach($companies as $company)
                                                            @if($oldCompanyInput == $company->id)
                                                                <option value="{{ $company->id }}" selected>{{ $company->name }}</option>
                                                            @else
                                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="location">Location</label>
                                                    <select name="location" id="location" @if(is_null(old('company'))) disabled @endif class="form-control" data-selected-location="{{ old('location') }}">
                                                        <option value="">Select a location</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @elseif($authUser->can('create_location'))
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="location">Location</label>
                                                    <select name="location" id="location" class="form-control">
                                                        <option value="">Select a location</option>
                                                        @foreach($locations as $location)
                                                            @if(old('location') == $location->id)
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
                                                <label for="name">Full Name</label>
                                                <input type="text" name="name" id="name" required value="{{ old('name') }}" class="form-control" placeholder="Enter full name">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" name="email" id="email" required value="{{ old('email') }}" class="form-control" placeholder="Enter email address">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select required name="gender" id="gender" class="form-control">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="phone">Contact Phone</label>
                                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}"
                                                       minlength="11" maxlength="14" data-rule-validphone required
                                                       onkeypress="return allowOnlyPhoneCharacters(event);" placeholder="Phone Number">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address">Contact Address</label>
                                                <textarea name="address" required id="address" class="form-control">{{ old('address') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <?php
                                            $oldRoles = old('roles');
                                            ?>
                                            <div class="form-group">
                                                <label for="roles">Role</label><br>
                                                <div class="card">
                                                    @foreach($roles as $role)
                                                        <fieldset class="checkbox">
                                                            <label>
                                                                <input type="checkbox" @if($oldRoles && in_array($role->id, $oldRoles)) checked @endif name="roles[]" value="{{$role->id}}" style="margin-right: 10px"> {{ $role->display_name }}
                                                            </label>
                                                        </fieldset>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary col-md-2">
                                        <em class="la la-check-square-o"></em> Save
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
    <script defer src="{{asset('js/employee-form-interactivity.js')}}" type="text/javascript"></script>
@endsection
