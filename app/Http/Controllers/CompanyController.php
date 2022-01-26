<?php

namespace App\Http\Controllers;

use App\Classes\StatisticsFilters;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Rules\ValidPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{


    public function __construct()
    {
        $this->middleware('permission:create_company', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_company', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_company', ['only' => ['deactivate','delete']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::all();
        return view('company.list', compact('companies'));
    }

    public function getCompanyLocations(Request $request, Company $company)
    {
        $locations = Location::where('company_id', $company->id)->get();
        return response()->json($locations, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('company.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $authUser = $this->getAuthUser();
        if($request->has('phone')) {
            $request['phone'] = cleanUpPhone($request->phone);
        }
        $this->validate($request, [
            'company_name' => 'unique:companies,name',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'unique:employees,email',
            'phone' => ['required', new ValidPhone, 'unique:employees,phone']
        ]);

        DB::beginTransaction();
        $company = Company::create([
            'name' => $request->company_name,
            'is_active' => 1
        ]);
        $company_owner = Employee::create([
            "email" => $request->email,
            "phone" => $request->phone,
            "name" => $request->first_name . ' ' . $request->last_name,
            "created_by" => $authUser->id,
            "company_id" => $company->id,
            'is_active' => 1
        ]);
        $company_owner->assignARole('super_admin');

        $company->update(['owner_id' => $company_owner->id]);

        $authUser->recordActivity([
            [
                'name' => 'created_company',
                'url' => route('company.view', ['company' => $company->id]),
                'description' => 'Added a company: '. $company->name
            ],
            [
                'name' => 'created_employee',
                'url' => route('employee.view', ['employee' => $company_owner->id]),
                'description' => 'Added an employee: ' . $company_owner->email
            ]
        ]);
        DB::commit();

        // Successful save should redirect to all companies view
        return redirect(route('company.list'))->with(['status' => 'success', 'message' => 'Company added successfully', 'title' => 'OK']);
    }

    /**
     * Display the specified resource.
     *
     * @param  Company $company,
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function view(Company $company, Request $request)
    {
        if($request->hasAny(['order_location', 'order_type', 'payment_method'])) {
            $this->validate($request, [
                'order_location' => 'nullable|exists:locations,id',
                'order_type' => 'nullable|exists:order_types,id',
                'payment_method' => 'nullable|exists:payment_methods,id'
            ]);
        }
        elseif($request->hasAny(['employee_company', 'employee_location', 'employee_role'])) {
            $this->validate($request, [
                'employee_company' => 'nullable|exists:companies,id',
                'employee_location' => 'nullable|exists:locations,id',
                'employee_role' => 'nullable|exists:roles,id',
            ]);
        }
        $authUser = $this->getAuthUser();

        $statistics = (new StatisticsFilters($request, $authUser))->generalStatistics(['company' => $company->id]);
        $companyOrders = $this->getFilteredOrders($request, 20, $company->id);
        $companyEmployees = $this->getFilteredEmployees($request, $authUser, 5, $company->id);
        $paymentMethods = PaymentMethod::all();
        $orderTypes = OrderType::all();
        $roles = Role::getAllowed()->get();
        return view('company.view', compact('company', 'statistics','companyOrders', 'authUser', 'paymentMethods', 'orderTypes', 'companyEmployees', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('company.edit', compact('company'));
    }

    /**
     * Update the specified company details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Company $company)
    {
        if($request->has('phone')) {
            $request['phone'] = cleanUpPhone($request->phone);
        }
        $this->validate($request, [
            'company_name' => 'unique:companies,name,'.$company->id,
            'owner_name' => 'required',
            'email' => 'unique:employees,email,'. $company->owner->id,
            'phone' => ['required', new ValidPhone, 'unique:employees,phone,'. $company->owner->id]
        ]);

        DB::beginTransaction();
        $company_owner = $company->owner;
        $company_owner->update([
            "email" => $request->email,
            "phone" => $request->phone,
            "name" => $request->owner_name,
        ]);
        $company->update([
            'name' => $request->company_name
        ]);

        auth()->user()->recordActivity([
            [
                'name' => 'updated_company',
                'url' => route('company.view', ['company' => $company->id]),
                'description' => 'Updated a company: '. $company->name
            ],
            [
                'name' => 'updated_employee',
                'url' => route('employee.view', ['employee' => $company_owner->id]),
                'description' => 'Updated an employee: ' . $company_owner->email
            ]
        ]);
        DB::commit();
        return redirect(route('company.view', ['company' => $company->id]))->with(['status' => 'success', 'message' => 'Company updated successfully', 'title' => 'OK']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Company $company
     * @return \Illuminate\Http\Response
     */
    public function deactivate(Company $company)
    {
        if(!$company->_isActive()) {
            return redirect()->route('company.view', ['company' => $company->id])->with(['status' => 'warn', 'message' => 'Company Already Inactive', 'title' => 'OK']);
        }
        $company->update([
            'is_active' => 0
        ]);
        auth()->user()->recordActivity([
            [
                'name' => 'deactivated_company',
                'url' => route('company.view', ['company' => $company->id]),
                'description' => 'Deactivated a company: ' . $company->name
            ]
        ]);
        return redirect()->route('company.view', ['company' => $company->id])->with(['status' => 'success', 'message' => 'Company Deactivated Successfully', 'title' => 'OK']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Company $company
     * @return \Illuminate\Http\Response
     */
    public function activate(Company $company)
    {
        if($company->_isActive()) {
            return redirect()->route('company.view', ['company' => $company->id])->with(['status' => 'warn', 'message' => 'Company Already Active', 'title' => 'OK']);
        }
        $company->update([
            'is_active' => 1
        ]);
        auth()->user()->recordActivity([
            [
                'name' => 'reactivated_company',
                'url' => route('company.view', ['company' => $company->id]),
                'description' => 'Reactivated a company: ' . $company->name
            ]
        ]);
        return redirect()->route('company.view', ['company' => $company->id])->with(['success_message', 'Company Reactivated Successfully']);
    }

    /**
     *
     * @param Request $request
     * @param Company $company
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request,Company $company)
    {
        $this->validate($request, [
            'deletion_type' => array(
                'required',
                Rule::in(['temporary', 'permanent'])
            )
        ]);
        $description = 'Deleted a company: ';
        DB::beginTransaction();
        try {
            if ($request->deletion_type === 'permanent') {
                $owner = $company->owner();
                $company->update(['owner_id' => null]);
                $company->forceDelete();
                $owner->forceDelete();
                $description = 'Permanently Deleted a company: ';
            } else {
                $company->delete();
            };
            auth()->user()->recordActivity([
                [
                    'name' => 'deleted_company',
                    'description' => $description . $company->name
                ]
            ]);
            DB::commit();
            return redirect()->route('company.list')->with(['status' => 'success', 'message' =>'Company Deleted Successfully', 'title' => 'OK']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('company.list')->with(['status' => 'error', 'message' => 'Company deletion failed', 'title' => 'Oops!!']);
        }
    }
}
