@extends('layouts.app')

@section('title', 'General Settings')

@section('navigation')
    @component('components.navigation.super_admin_navigation');
    @endcomponent
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title" id="basic-layout-form">Settings</h1>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" method="post" action="{{ route('settings.update_recipients') }}">
                                @csrf
                                <div class="form-body">
                                    <h2 class="form-section"><em class="ft-user"></em> Recipients of Daily and Monthly Report</h2>
                                    <div class="table-wrapper">
                                        <table class="table table-striped table-bordered text-truncate" id="recipientsTable">
                                            <thead>

                                            <tr>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Role</th>
                                                @if(!$authUser->company_id)
                                                    <th scope="col">Company</th>
                                                @endif
                                                <th scope="col">Click to Add/Remove</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($employees as $employee)
                                            <tr>
                                                <td>{{ $employee->name }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>{!! $employee->getRoles() !!}</td>
                                                @if($authUser->can('edit_company'))
                                                    <td>{{ $employee->company ? $employee->company->name : ''}}</td>
                                                @endif
                                                <td class="text-center">
                                                    <input type="checkbox" @if($employee->receive_reports) checked @endif name="report_recipients[{{ $employee->id }}]">
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ !$authUser->company_id ? 4 : 5 }}">No data to display!</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group text-center">
                                                <button type="submit" class="btn btn-primary"> Save Changes </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <form id="generate_users" class="form" method="post" action="{{ route('settings.generate_users') }}">
                                @csrf
                                <div class="form-body">
                                    <h2 class="form-section mt-2"><i class="la la-paperclip"></i>Get a list of specific Users</h2>
{{--                                    <span class="small">***The result would be sent to your email as an attachment</span>--}}
                                    <div class="row">
{{--                                        <div class="col-12">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label for="filter_company">Company</label>--}}
{{--                                                <select name="company" id="filter_company" class="form-control">--}}
{{--                                                    <option value="">Select Company</option>--}}
{{--                                                    @foreach($companies as $company)--}}
{{--                                                        <option value="{{ $company->id }}">{{ $company->name }}</option>--}}
{{--                                                    @endforeach--}}
{{--                                                </select>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-12">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <label for="filter_location">Location</label>--}}
{{--                                                <select name="location" id="filter_location" class="form-control">--}}
{{--                                                    <option value="">Select Location</option>--}}
{{--                                                    @foreach($locations as $location)--}}
{{--                                                        <option value="{{ $location->id }}">{{ $location->name }}</option>--}}
{{--                                                    @endforeach--}}
{{--                                                </select>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                        <div class="col-12 text-center">
                                            @php
                                                $present_date = now()->toDateString();
                                            @endphp
                                            <label>Range of Last Activity</label><br>
                                            <span class="small">**Filter by the last time an order was initiated or completed by users</span>
                                            <div class="row mt-1">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Start Date</label>
                                                        <input type="date" name="last_activity_start_at" autocomplete="off" value="{{ $present_date }}" id="start_date" class="form-control" placeholder="Start Date">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>End Date</label>
                                                        <input type="date" name="last_activity_end_at" value="{{ $present_date }}" autocomplete="off" id="end_date" class="form-control" placeholder="End Date">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group text-center">
                                                <button type="button" onclick="generateUsersList()" class="btn btn-primary">Generate Result </button>
                                            </div>
                                        </div>
                                    </div>
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
    <script src="{{asset('js/pages/general_settings.js')}}"></script>
@endsection
