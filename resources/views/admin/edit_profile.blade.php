@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <?php
        $authUser = Auth::user();
    ?>
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Edit Employee</h4>

                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" action="{{route('employee.update', ['employee' => 3])}}" method="post">
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        @if($authUser->can('create-company'))
                                        <div class="col-md-12">

                                            <div class="form-group">
                                                <label for="company_select">Company</label>
                                                <select name="company" id="company_select" class="form-control">
                                                    <option>Ezwash-main</option>
                                                    <option selected>Company 1</option>
                                                    <option>Company 2</option>
                                                    <option>Company 2</option>
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                        @if($authUser->can('create-location'))
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput1">Location</label>
                                                <select name="location" id="employee_location" class="form-control">
                                                    <option>Select Location</option>
                                                    <option>Yaba</option>
                                                    <option>Ikota</option>
                                                    <option selected>Lekki</option>
                                                    <option>Osapa</option>
                                                    <option>Ilupeju</option>
                                                    <option>Maryland</option>
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput2">Full Name</label>
                                                <input type="text" name="name" id="name" class="form-control" value="Folabi Adekunle" placeholder="Enter full name">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select name="gender" id="gender" class="form-control">
                                                    <option>Select Gender</option>
                                                    <option>Male</option>
                                                    <option selected>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="phone">Contact Phone</label>
                                                <input type="text" name="email" id="phone" class="form-control" value="<?= old('phone') ? old('phone') : $authUser->phone; ?>" value="08090781232" placeholder="Phone Number" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address">Contact Address</label>
                                                <textarea name="address" id="address" class="form-control">
                                                    {{ $authUser->address}}
                                                </textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="role">Role</label>
                                                <select name="role" id="role" class="form-control" required>
                                                    <option value="">Select Role</option>
                                                    @foreach($roles as $role)
                                                        @if($authUser->hasRole($role->name))
                                                        <option value="{{ $role->id }}" selected>{{ $role->display_name }}</option>
                                                        @else
                                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
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
