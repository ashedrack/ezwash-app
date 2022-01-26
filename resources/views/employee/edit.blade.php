@extends('layouts.app')

@section('title', 'Edit Employee')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title" id="basic-layout-form">Edit Employee</h1>

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
                            <form class="form" action="{{route('employee.update', ['employee' => $employee->id])}}" method="post">
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        @if($authUser->can('create_company'))
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="company">Company</label>
                                                    <?php
                                                    $oldCompanyInput = old('company', $employee->company_id);
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
                                                    <select name="location" id="location" @if(is_null(old('company', $employee->company_id))) disabled @endif
                                                        class="form-control" data-selected-location="{{ old('location', $employee->location_id) }}">
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
                                                            @if(old('location', $employee->location_id) == $location->id)
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
                                                <input type="text" name="name" id="name" required value="{{ old('name', $employee->name) }}" class="form-control" placeholder="Enter full name">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input disabled type="email" name="email" id="email" required value="{{ old('email', $employee->email) }}" class="form-control" placeholder="Enter email address">
                                                <input hidden type="email" name="email" value="{{ old('email', $employee->email) }}">

                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select required name="gender" id="gender" class="form-control">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="phone">Contact Phone</label>
                                                <input type="text" name="phone" required id="phone" class="form-control" value="{{ old('employee', $employee->phone) }}"
                                                				minlength="11" maxlength="14" data-rule-validphone
                                                       			onkeypress="return allowOnlyPhoneCharacters(event);" placeholder="Phone Number">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address">Contact Address</label>
                                                <textarea name="address" required id="address" class="form-control">{{ old('address', $employee->address) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <?php
                                                $oldRoles = old('roles', $employee->roles->pluck('id')->toArray());
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
    <script defer src="{{asset('js/employee-form-interactivity.js')}}" type="text/javascript"></script>
@endsection
