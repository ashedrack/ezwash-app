<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Role;
use App\Rules\EmployeeCompanyRequiredAndExists;
use App\Rules\EmployeeLocationRequiredAndExists;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create_employee', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_employee', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_employee', ['only' => ['delete']]);
        $this->middleware('permission:deactivate_employee', ['only' => ['deactivate', 'activate']]);
    }

    /**
     * Ensure that current user has permission to carry out certain actions
     *
     * @param (int) $employee_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cantakeAction($employee_id)
    {
        $authUser = $this->getAuthUser();
        if($authUser->id === $employee_id){
            return redirect()->route('admin.profile');
        }
        $employee = Employee::getAllowed()->whereHas('roles', function ($q) use($authUser){
            $maxHierarchy = $authUser->roles->max('hierarchy');
            $q->where('hierarchy', '<=', $maxHierarchy);
        })->where('id', $employee_id)->first();
        if(empty($employee)){
            return abort(403, 'Permission Denied!!');
        }
    }

    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        if($request->hasAny(['employee_company', 'employee_location', 'employee_role', 'employee_name_or_email'])) {
            $this->validate($request, [
                'employee_company' => 'nullable|exists:companies,id',
                'employee_location' => 'nullable|exists:locations,id',
                'employee_name_or_email' => 'nullable',
                'employee_role' => 'nullable|exists:roles,id',
            ]);
        }
        $companies = Company::all();
        $locations = Location::getAllowed();
        $roles = Role::getAllowed()->get();
        $authUser = $this->getAuthUser();
        $employees = $this->getFilteredEmployees($request, $authUser, 20);
        return view('employee.list', compact('employees', 'authUser', 'companies', 'locations', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $authUser = $this->getAuthUser();
        $companies = Company::has('locations', '>', 0)->get();
        $locations = Location::allowedToAccess($authUser)->where('is_active', 1);
        $roles = Role::getAllowed()->get();
        return view('employee.create', compact('companies', 'locations', 'roles', 'authUser'));
    }

    /**
     * Store a newly created employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $authUser = $this->getAuthUser();
        if(!$authUser->can('create_company')){
            $request['company'] = $authUser->company->id;
        }
        if(!$authUser->can('create_location')){
            $request['location'] = $authUser->location->id;
        }
        $roles = Role::get()->pluck('hierarchy', 'name')->toArray();

        $this->validate($request, [
            'roles.*' => 'bail|required|exists:roles,id',
            'company' => [new EmployeeCompanyRequiredAndExists($roles)],
            'location' => [new EmployeeLocationRequiredAndExists($roles)],
            'name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'phone' => ['bail','required', new ValidPhone,
                function ($attribute, $value, $fail) {
                    $phone = cleanUpPhone($value);
                    if(Employee::where('phone', $phone)->count() > 0){
                        $fail("The phone number has already been taken");
                    }
                }
            ],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'address' => 'required'
        ]);

        $phone = cleanUpPhone($request->phone);

        $roles = Role::getAllowed()->whereIn('id', $request->roles)->get();
        if(empty($roles)){
            return redirect()
                ->back()
                ->withErrors(['roles' => 'Invalid roles selected'])
                ->withInput();
        }
        $employee = Employee::create([
            'email' => $request->email,
            'phone' => $phone,
            'name' => $request->name,
            'gender' => $request->gender,
            'address' => $request->address,
            'created_by' => $authUser->id,
            'location_on_create' => $request->location,
            'location_id' => $request->location,
            'company_id'=> $request->company,
            'is_active' => 1,
        ]);
        $employee->attachRoles($roles);
        $authUser->recordActivity([
            [
                'name' => 'created_employee',
                'url' => route('employee.view', ['employee' => $employee->id]),
                'description' => 'Created an employee: '. $employee->name
            ]
        ]);
        return redirect(route('employee.list'))->with(['status' => 'success','title' => 'OK', 'message' => 'Employee added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee $employee
     * @return \Illuminate\Http\Response
     */
    public function view(Employee $employee)
    {
        $employee = Employee::getAllowed()->where('id', $employee->id)->first();
        if(empty($employee)){
            return abort(403,'Permission denied');
        }
        $employeeActivities = $employee->activities()->orderBy('created_at', 'desc')->simplePaginate(20, ['*'], 'activities_page');
        $authUser =  $this->getAuthUser();
        return view('employee.view', compact('employee', 'employeeActivities', 'authUser'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        $authUser = $this->getAuthUser();
        if($employee->id === $authUser->id){
            return redirect()->route('admin.profile');
        }
        $employee = Employee::getAllowed()->where('id', $employee->id)->first();
        if(empty($employee)){
            return abort(403, 'Permission Denied!!');
        }
        $companies = Company::has('locations', '>', 0)->get();
        $locations = Location::getAllowed();
        $roles = Role::getAllowed()->get();
        return view('employee.edit', compact('employee','companies', 'locations', 'roles', 'authUser'));
    }

    /**
     * Update a employee's record
     *
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Employee $employee)
    {
        $authUser = $this->getAuthUser();
        if(!$authUser->can('create_company')){
            $request['company'] = $authUser->company->id;
        }
        if(!$authUser->can('create_location')){
            $request['location'] = $authUser->location->id;
        }

        //get this here to prevent repeating this in the EmployeeCompanyRequiredAndExists and EmployeeLocationRequiredAndExists Rules
        $roles = Role::get()->pluck('hierarchy', 'name')->toArray();

        $this->validate($request, [
            'roles.*' => 'bail|required|exists:roles,id',
            'company' => [new EmployeeCompanyRequiredAndExists($roles)],
            'location' => [new EmployeeLocationRequiredAndExists($roles)],
            'name' => 'required|string',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => ['bail','required', new ValidPhone,
                function ($attribute, $value, $fail) use ($employee) {
                    $phone = cleanUpPhone($value);
                    if(Employee::where('id', '<>', $employee->id)->where('phone', $phone)->count() > 0){
                        $fail("The phone number has already been taken");
                    }
                }
            ],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'address' => 'required',
        ]);

        $phone = cleanUpPhone($request->phone);
        $roles = Role::getAllowed()->whereIn('id', $request->roles)->get();
        if(empty($roles)){
            return redirect()
                ->back()
                ->withErrors(['roles' => 'Invalid roles selected'])
                ->withInput();
        }
        $employee->update([
            'email' => $request->email,
            'phone' => $phone,
            'name' => $request->name,
            'gender' => $request->gender,
            'address' => $request->address,
            'created_by' => $authUser->id,
            'location_id' => $request->location,
            'company_id'=> $request->company,
        ]);
        $employee->detachRoles($employee->roles);//Detach roles first
        $employee->attachRoles($roles);
        $authUser->recordActivity([
            [
                'name' => 'updated_employee',
                'url' => route('employee.view', ['employee' => $employee->id]),
                'description' => 'Updated an employee: '. $employee->name
            ]
        ]);
        return redirect()->route('employee.list')->with(['status' => 'success','title' => 'OK', 'message' => 'Employee updated successfully']);
    }

    /**
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deactivate(Request $request, Employee $employee)
    {
        $this->cantakeAction($employee->id);
        $employee->update([
            'is_active' => 0
        ]);

        auth()->user()->recordActivity([
            [
                'name' => 'deactivated_employee',
                'url' => route('employee.view', ['employee' => $employee->id]),
                'description' => 'Deactivated a employee: ' . $employee->name
            ]
        ]);
        return redirect(route('employee.view', ['employee' => $employee->id]))->with(['status' => 'success','title' => 'OK', 'message' => 'Employee deactivated successfully']);
    }
    /**
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function reactivate(Request $request, Employee $employee)
    {
        $this->cantakeAction($employee->id);
        $employee->update([
            'is_active' => 1
        ]);
        auth()->user()->recordActivity([
            [
                'name' => 'reactivated_employee',
                'url' => route('employee.view', ['employee' => $employee->id]),
                'description' => 'Reactivated a employee: ' . $employee->name
            ]
        ]);
        return redirect(route('employee.view', ['employee' => $employee->id]))->with(['status' => 'success','title' => 'OK', 'message' => 'Employee is now active']);
    }

    /**
     * Remove the specified employee from the database
     *
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request, Employee $employee)
    {
        $this->cantakeAction($employee->id);
        $this->validate($request, [
            'deletion_type' => array(
                'required',
                Rule::in(['temporary', 'permanent'])
            )
        ]);
        if($employee->companies()->count() > 0){
            return redirect()->back()->with(['status' => 'error', 'message' => 'Cannot delete a company owner except the company is deleted, or the ownership is changed', 'title' => 'Action Blocked!!']);
        }
        if ($request->deletion_type === 'permanent') {
            $employee->forceDelete();
        } else {
            $employee->delete();
        }
        return redirect()->back()->with(['status' => 'success', 'message' => 'Employee deleted successfully', 'title' => 'OK']);

    }
}
