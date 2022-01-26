@extends('layouts.app')

@section('title', 'All Employees')
@section('content')

    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">All Employees</h1>
                        @if(auth()->user()->can('create_employee'))
                        <a href="{{ route('employee.add') }}" class="btn btn-outline-primary btn-md float-right"><i class="la la-plus" style="font-size: inherit;"></i> Add Employee</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-content collapse show">
                        <div class="card-body card-dashboard">
                            <?php
                                $filterOptions = [];
                                if(!$authUser->company_id){
                                    $filterOptions['companies'] = $companies;
                                }
                                if(!$authUser->location_id){
                                    $filterOptions['locations'] = $locations;
                                }
                                if($roles->count() > 0){
                                    $filterOptions['roles'] = $roles;
                                }
                                $filterUrl = route('employee.list');
                                $queryParams = (!empty($requestQuery))? $requestQuery : [] ;
                            ?>
                            @component(
                                'components.employees_list', [
                                    'authUser' => $authUser,
                                    'filterUrl' => route('employee.list'),
                                    'allEmployees' => $employees,
                                    'allLocations' => $locations,
                                    'allCompanies' => $companies,
                                    'allRoles' => $roles,
                                ]
                            )
                            @endcomponent
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @component('components.deletion-prompt-template', [
        'permanentDeletionWarning' => 'PLEASE NOTE THAT THIS WILL DELETE THE EMPLOYEE PERMANENTLY FROM THE SYSTEM'
    ])
    @endcomponent
@endsection

@section('more-scripts')
    <script defer src="{{asset('js/pages/employee-list-component.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/deletion_helper.js') }}"></script>
    <script src="{{ asset('js/pages/list-employees.js') }}"></script>
@endsection

