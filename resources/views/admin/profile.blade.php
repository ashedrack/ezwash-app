@extends('layouts.app')

@section('title', 'View Employee')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <!-- Users Statistics -->
            <div class="row">
                <div class="col-xl-4 col-12" style="margin: auto">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-content collapse show bg-hexagons">
                                    <div class="card-body profile-image-wrapper pt-0" style="height: fit-content;">
                                        <div class="profile-image-circle" style="background-image: url('{{ asset('images/employee_profile_image1.jpg') }}');">

                                        </div>
                                        <p class="employee-profile-description"> {{ $employee->name }}</p>
                                        <p class="employee-profile-description"><span class="underline">Role:</span> {{ $employee->getRolesAsString() }}</p>
                                        @if(!empty($employee->company_id))
                                            <p class="employee-profile-description"><span class="underline">Company:</span> {{ $employee->company->name }}</p>
                                        @endif
                                        @if(!empty($employee->location_id))
                                            <p class="employee-profile-description"><span class="underline">Location:</span> {{ $employee->location->name }}</p>
                                        @endif
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
@endsection

@section('more-scripts')
    <script>
        $('#recentActivities').DataTable({
            searchable: false,
            language : {
                emptyTable: "No Activities Found"
            },
            paging: false,
            searching:false,
            info:false
        });
    </script>
@endsection
