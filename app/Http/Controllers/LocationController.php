<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:create_location', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_location', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_location', ['only' => ['deactivate','delete']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authUser = $this->getAuthUser();
        $companies = ($authUser->can('list_companies'))? Company::whereHas('locations')->with('locations')->get(): null;
        $locations = Location::allowedToAccess($authUser)->paginate();
        return view('location.list', compact('locations', 'companies', 'authUser'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $authUser =$this->getAuthUser();
        $companies = [];
        $placesApiKey = config('app.GOOGLE_PLACES_API_KEY');
        if($authUser->can('list_companies')){
            $companies = Company::all();
            return view('location.create', compact('companies', 'placesApiKey'));
        }
        $company = $authUser->company;
        return view('location.create', compact('company', 'placesApiKey'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        if($request->has('phone')) {
            $request['phone'] = cleanUpPhone($request->phone);
        }
        $companyID = $request->company ?? auth()->user()->company->id;
        $this->validate($request, [
            'company' => 'nullable|exists:companies,id',
            'name' => ['required', Rule::unique('locations', 'name')->where('company_id', $companyID)],
            'address' => 'required|string|min:5',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => ['required', new ValidPhone, 'unique:locations,phone'],
            'lockers_count' => 'required|integer|max:50,min:3',
        ]);
        $company_id = $request->company;
        if(!$company_id){
            if(isset(auth()->user()->company)){
                $company_id = auth()->user()->company->id;
            }else{
                return redirect()->back()->with([
                    'status' => 'error', 'message' => 'Unable to add location','title' => 'Oops!!'
                ])->withInput();
            }
        }

        Location::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'number_of_lockers' => $request->lockers_count,
            'company_id' => $company_id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'is_active' => 1,
        ]);
        return redirect()->route('location.list')->with(['status' => 'success', 'message' => 'Location added successfully', 'title' => 'OK']);
    }

    /**
     * Display the specified resource.
     *
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function view(Location $location, Request $request)
    {
        $authUser = $this->getAuthUser();
        if(!$authUser->canViewLocation($location->id)){
            return redirect()->route('home')->with([
                'status' => 'error',
                'message' => 'Not allowed to view this location',
                'title' => 'Permission Denied'
            ]);
        }
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
        $location = Location::with(['company', 'orders', 'employees'])->where('id', $location->id)->first();
        $request['order_location'] = $location->id;
        $request['employee_location'] = $location->id;
        $locationOrders = $this->getFilteredOrders($request, 20);
        $locationEmployees = $this->getFilteredEmployees($request, $authUser, 20);
        $paymentMethods = PaymentMethod::all();
        $orderTypes = OrderType::all();
        $roles = Role::getAllowed()->get();
        return view('location.view', compact('authUser', 'location', 'locationOrders', 'authUser','orderTypes','paymentMethods', 'locationEmployees', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $location)
    {
        $authUser = $this->getAuthUser();
        $placesApiKey = config('app.GOOGLE_PLACES_API_KEY');
        if(!$authUser->canViewLocation($location->id)){
            return redirect()->route('home')->with([
                'status' => 'error',
                'message' => 'Not allowed to edit this location',
                'title' => 'Permission Denied'
            ]);
        }
        if($authUser->can('list_companies')){
            $companies = Company::all();
            return view('location.edit', compact('location','companies', 'placesApiKey'));
        }
        $company = $authUser->company;
        return view('location.edit', compact('location','company', 'placesApiKey'));
    }

    /**
     * Update the specified location in database.
     *
     * @param  Request $request
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        $authUser = $this->getAuthUser();
        if($request->has('phone')) {
            $request['phone'] = cleanUpPhone($request->phone);
        }
        $this->validate($request, [
            'company' => 'nullable|exists:companies,id',
            'name' => 'required|string|unique:locations,name,'.$location->id,
            'address' => 'required|string|min:5',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => ['required', new ValidPhone, 'unique:locations,phone,'.$location->id],
            'lockers_count' => 'required|integer|max:50,min:3',
        ]);
        $company_id = $request->company;
        if(!$company_id){
            if(isset($authUser->company)){
                $company_id = $authUser->company->id;
            }else{
                return redirect()->back()->with([
                    'status' => 'error', 'message' => 'Unable to update location','title' => 'Oops!!'
                ])->withInput();
            }
        }
        //Check if number_of_lockers was changed to a lower value
        if($location->number_of_lockers > $request->lockers_count) {
            $lockersAboveRange = $location->lockers()
                ->where('occupied', 1)
                ->where('locker_number', '>', (int)$request->lockers_count)->count();
            if($lockersAboveRange > 0){
                return redirect()->back()
                    ->withErrors(['invalid_locker_number' => "$lockersAboveRange lockers above specified 'number of lockers' currently in use"])
                    ->withInput();
            }
        }
        $location->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'number_of_lockers' => $request->lockers_count,
            'company_id' => $company_id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
        ]);
        return redirect(route('location.view', ['location' => $location->id]))->with(['type' => 'success', 'message' => 'Location updated successfully']);
    }

    /**
     * Deactivate the specified location
     *
     * @param  Request $request
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function deactivate(Request $request, Location $location)
    {
        $authUser = $this->getAuthUser();
        if(!empty($authUser->location) && $authUser->location_id === $location->id){
            return redirect()->back()
                ->with(['status' => 'error', 'Messages' => 'Cannot deactivate your location', 'title' => 'Permission Denied!!'])
                ->withInput();
        }
        $location->deactivate();
        return redirect(route('location.list'))->with(['type' => 'success', 'message' => 'Location deactivated successfully']);
    }

    /**
     * Activate the specified location
     *
     * @param  Request $request
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request, Location $location)
    {
        $authUser = $this->getAuthUser();
        if(!empty($authUser->location) && $authUser->location_id === $location->id){
            return redirect()->back()
                ->with(['status' => 'error', 'Messages' => 'Cannot activate your location', 'title' => 'Permission Denied!!'])
                ->withInput();
        }
        $location->activate();
        return redirect(route('location.list'))->with(['type' => 'success', 'message' => 'Location activated successfully']);
    }

    /**
     * Temporarily Or Permanently remove the specified location from the database.
     *
     * @param Request $request
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, Location $location)
    {
        $authUser = $this->getAuthUser();
        if(!empty($authUser->location) && $authUser->location->id === $location->id){
            return redirect()->back()
                ->with(['status' => 'error', 'Messages' => 'Cannot delete your location', 'title' => 'Permission Denied!!'])
                ->withInput();
        }
        $this->validate($request, [
            'deletion_type' => array(
                'required',
                Rule::in(['temporary', 'permanent'])
            )
        ]);
        DB::beginTransaction();
        try {
            if ($request->deletion_type === 'permanent') {
                $location->forceDelete();
            } else {
                $location->lockers()->delete();
                $location->delete();
            }
            DB::commit();
            return redirect(route('location.list'))->with(['status' => 'success', 'message' => 'Location deleted successfully', 'title' => 'OK']);
        } catch(\Exception $e){
            DB::rollback();
            return redirect()->back()->withErrors(['deletion_failed' => 'An error occurred: Unable to delete location'])
                ->withInput();
        }
    }
}
