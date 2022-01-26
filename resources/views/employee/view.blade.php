@extends('layouts.app')

@section('title', 'View Employee')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <div class="row">
                <div class="col-xl-4 col-12" style="margin: auto">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-content collapse show bg-hexagons">
                                    <div class="card-body profile-image-wrapper pt-0" style="height: fit-content;">
{{--                                        <div class="profile-image-circle" style="background-image: url('{{ asset('images/employee_profile_image1.jpg') }}');">--}}

{{--                                        </div>--}}
                                        <br>
                                        <h1 class="employee-profile-descrsiption"> {{ $employee->name }}</h1>
                                        <br><br>
                                        <p class="employee-profile-description"><span class="underline">Phone:</span> {{ $employee->phone }}</p>
                                        <p class="employee-profile-description"><span class="underline">Role(s):</span> {{ $employee->getRolesAsString() }}</p>
                                        @if(!empty($employee->location))
                                        <p class="employee-profile-description"><span class="underline">Location:</span> {{ $employee->location->name }}</p>
                                        @endif
                                        @if($authUser->can('list-companies') && !empty($employee->company))
                                            <p class="employee-profile-description"><span class="underline">Company:</span> {{ $employee->company->name }}</p>
                                        @endif
                                        <div class="employee-manage-btns">
                                            <a href="{{ route('employee.edit', ['employee' => $employee->id]) }}" class="btn btn-outline-secondary width-100 mr-1">Edit</a>
                                            @if($employee->is_active == 1)
                                                <button onclick="EzwashHelper.deactivationWarning('actionForm', '{{ route('employee.deactivate', ['employee' => $employee->id]) }}', 'Current activities by this employee will be halted and login attempts blocked');" class="btn btn-outline-warning float-right width-100">Deactivate</button>
                                            @else
                                                <button onclick="EzwashHelper.reactivationWarning('actionForm', '{{ route('employee.activate', ['employee' => $employee->id]) }}', 'Employee will be able to login and resume operations')" class="btn btn-outline-warning float-right width-100">Activate</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div id="recent-activity" class="col-12">
                    @component('components.activity-log', [
                        'allActivities' => $employeeActivities
                    ])
                    @endcomponent
                </div>
            </div>
        </div>
    </div>

    <form id="actionForm" method="post" style="display:none;">
        @csrf
    </form>
@endsection

@section('more-scripts')
{{--<script>--}}
    {{--(function ($) {--}}
        {{--$('#recentActivities').DataTable({--}}
            {{--language : {--}}
                {{--emptyTable: "No Activities Found"--}}
            {{--},--}}
            {{--paging: false,--}}
            {{--searching:false,--}}
            {{--info:false--}}
        {{--});--}}
    {{--})(jQuery);--}}
{{--</script>--}}
@endsection
