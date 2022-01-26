<?php
$queryParams = session('employeeFilterParams');
?>
<div class="card-body">
    {{--@if($allEmployees->total() > 0)--}}
        <div class="card">

            <div class="card-title">
                <button class="btn btn-primary float-right" id="toggle_filter_section">Filter Employees <span i class="la la-toggle-down"></span></button>
            </div>
            <div id="employee_filter_section">

                <form id="employees_filter" action="{{ $filterUrl }}" method="get">
                    <div class="form-body container">
                        @if(isset($hiddenFields) && !empty($hiddenFields))
                            @foreach($hiddenFields as $field)
                                <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
                            @endforeach
                        @endif
                        <p>Apply any or all of the filters below to streamline your search</p>
                        <div class="row">
                            @if(!$authUser->company_id && !empty($allCompanies))
                                <?php
                                $oldCompany = (array_key_exists('employee_company', $queryParams))? old('employee_company',$queryParams['employee_company']) : old('employee_company')
                                ?>
                                <div class="form-group col-md">
                                    <label for="employee_company">Company</label>
                                    <select name="employee_company" id="employee_company" class="form-control">
                                        <option value="">Select Location</option>
                                        @foreach($allCompanies as $c)
                                            <option value="{{ $c->id }}" {{ ($oldCompany == $c->id)? 'selected': '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if(!$authUser->location_id && !empty($allLocations))
                                <?php
                                $oldLocation = (array_key_exists('employee_location', $queryParams))? old('employee_location',$queryParams['employee_location']) : old('employee_location')
                                ?>
                                <div class="form-group col-md">
                                    <label for="employee_location">Location</label>
                                    <select name="employee_location" id="employee_location" class="form-control">
                                        <option value="">Select Location</option>
                                        @foreach($allLocations as $l)
                                            <option value="{{ $l->id }}" {{ ($oldLocation == $l->id)? 'selected': '' }}>{{ $l->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if(!empty($allRoles))
                                <?php
                                $oldRole = (array_key_exists('employee_role', $queryParams))? old('employee_role',$queryParams['employee_role']) : old('employee_role')
                                ?>
                                <div class="form-group col-md">
                                    <label for="employee_role">Role</label>
                                    <select name="employee_role" id="employee_role" class="form-control">
                                        <option value="">Select Role</option>
                                        @foreach($allRoles as $r)
                                            <option value="{{ $r->id }}" {{ ($oldRole == $r->id)? 'selected': '' }}>{{ $r->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <?php
                        $oldNameOrEmail = (array_key_exists('employee_name_or_email', $queryParams))? old('employee_name_or_email',$queryParams['employee_name_or_email']) : old('employee_name_or_email ')
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <input class="form-control" value="{{ $oldNameOrEmail }}" type="text" id="employee_name_or_email" name="employee_name_or_email" placeholder="Filter by employee name or email">
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6 p-0">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-danger w-100" id="toggle_filter_section">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    {{--@endif--}}
    {{$allEmployees->appends($queryParams)->links()}}
    <table class="table table-bordered" id="ordersListTable">
        <thead>
        <tr>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            @if(!$authUser->company_id)
                <th>Company</th>
            @endif
            @if(!$authUser->location_id)
                <th>Location</th>
            @endif
            <th>Date Added</th>
            <th>Actions</th>
        </tr>
        </tr>
        </thead>
        <tbody>
        @foreach($allEmployees as $employee)
            <tr @if($employee->is_active == 0) class="inactive-record-row" @endif>
                <td>{{ $employee->name}}</td>
                <td>{{ $employee->email }}</td>
                <td>{!! $employee->getRoles() !!}</td>
                @if(!$authUser->company_id)
                    <td>{{ $employee->company ? $employee->company->name : ''}}</td>
                @endif
                @if(!$authUser->location_id)
                    <td>{{ $employee->location ? $employee->location->name : ''}}</td>
                @endif
                <td class="p-1">{{ $employee->created_at }}</td>
                <td class="text-truncate">
                    @if ($employee->id == $authUser->id)
                        <a class="btn btn-sm btn-primary round" href="{{route('admin.profile')}}">View</a>
                    @else
                        <a class="btn btn-sm btn-primary round" href="{{route('employee.view', ['employee' => $employee->id])}}">View</a>
                        <a class="btn btn-sm btn-outline-secondary round" href="{{route('employee.edit', ['employee' => $employee->id])}}">Edit</a>
                        @if($authUser->can('delete_employee'))
                        <button type="button" data-deletion-prompt data-deletion-form="employeeDeletionForm" data-deletion-url="{{ route('employee.delete',['employee' => $employee->id]) }}" class="btn btn-sm btn-outline-danger round">Delete</button>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $allEmployees->appends($queryParams)->links() }}
</div>
<form method="post" id="employeeDeletionForm" style="display: none;">
    @csrf
    <input type="hidden" id="deletion_type" name="deletion_type">
</form>

