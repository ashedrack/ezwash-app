<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerAutocompleteResource;
use App\Http\Resources\UserCollection;
use App\Models\Company;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create_customer', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_customer', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_customer', ['only' => ['delete']]);
        $this->middleware('permission:deactivate_customer', ['only' => ['deactivate', 'activate']]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $authUser = $this->getAuthUser();
        $locations = Location::getAllowed();
        $queryParams = [
            'search_string' => null,
            'location' => null
        ];
        if($request->hasAny(['location', 'search_string'])){
            $queryParams = [
                'location' => $request->location,
                'search_string' => $request->search_string
            ];
            $users = $this->getFilteredUsers($request)->paginate(20);
        }else {
            $users = User::paginate(20);
        }
        return view('customer.list', compact('locations','users', 'authUser', 'queryParams'));
    }

    /**
     * Display a listing of the customers that fit a filter.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter()
    {
        return view('customer.list');
    }

    public function siteWideSearch(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'query_string' => 'required',
                'page' => 'nullable|numeric',
                'records_per_page' => 'nullable|numeric|min:1,max:50'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $records_per_page = ($request->has('records_per_page'))? $request->records_per_page: 20;
            $page = ($request->has('page'))? $request->page : null;

            $customers = new UserCollection($this->siteWideUserFilter($request->query_string, $records_per_page, $page));
            return response()->json([
                "status" => true,
                "data" => $customers->toArray($request)
            ], 200);

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = "Data sent failed to pass validation. " . implode(' ', $errors);
            return response()->json([
                "status" => false,
                "data" => [],
                "error" => $message
            ], 400);
        } catch (\Exception $e){

            return response()->json([
                "status" => false,
                "data" => [],
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::has('locations', '>', 0)->get();
        $locations = Location::getAllowed();
        $authUser = $this->getAuthUser();
        return view('customer.create', compact('companies', 'locations', 'authUser'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => ['bail','required', new ValidPhone,
                function ($attribute, $value, $fail) {
                    $phone = cleanUpPhone($value);
                    if(User::where('phone', $phone)->count() > 0){
                        $fail("The phone number has already been taken");
                    }
                }
            ],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'location' => ['required', 'exists:locations,id']
        ]);
        $phone = cleanUpPhone($request->phone);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'gender' => $request->gender,
            'location_id' => $request->location
        ]);
        return redirect()->route('customer.list')->with(['status' => 'success', 'message' => 'Customer added successfully', 'title' => 'OK']);
    }

    /**
     * Display the specified resource.
     *
     * @param  User $customer
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function view(User $customer, Request $request)
    {
        $customer = User::where('id', $customer->id)->with(['orders', 'activities'])->first();
        $transactions = $customer->transactionsByPaymentMethod();
        $customerOrders = $this->getFilteredOrders([
            'request' => $request,
            'records_per_page' => 20,
            'user_id' => $customer->id
        ]);
        $orderTypes = OrderType::all();
        $paymentMethods = PaymentMethod::all();
        $authUser = $this->getAuthUser();
        $customerActivities = $customer->activities()->orderBy('created_at', 'desc')->simplePaginate(20, ['*'], 'activities_page');
        return view('customer.view', compact('customer', 'transactions', 'customerOrders', 'orderTypes', 'paymentMethods','authUser', 'customerActivities'));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param User $customer
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(User $customer)
    {
        $authUser = $this->getAuthUser();
        $locations = Location::getAllowed();
        return view('customer.edit', compact('customer', 'authUser', 'locations'));
    }

    /**
     * Update the specified customer's info.
     *
     * @param Request $request
     * @param User $customer
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, User $customer)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'phone' => ['bail','required',new ValidPhone,
                function ($attribute, $value, $fail) use ($customer) {
                    $phone = cleanUpPhone($value);
                    if(User::where('id', '<>', $customer->id)->where('phone', $phone)->count() > 0){
                        $fail("The phone number has already been taken");
                    }
                }
            ],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'location' => ['required', 'exists:locations,id']
        ]);
        $phone = cleanUpPhone($request->phone);
        $customer->update([
            'name' => $request->name,
            'phone' => $phone,
            'gender' => $request->gender,
            'location_id' => $request->location
        ]);
        return redirect(route('customer.view', ['customer' => $customer->id]))->with(['status' => 'success', 'message' => 'Customer updated successfully', 'title' => 'OK']);
    }

    /**
     * Deactivate the specified customer.
     *
     * @param User $customer
     * @return \Illuminate\Http\Response
     */
    public function deactivate(User $customer)
    {
        if($customer->_isActive()) {
            $customer->deactivate();
        }
        return redirect(route('customer.list'))->with(['status' => 'success', 'message' => 'Customer deactivated successfully', 'title' => 'OK']);
    }
    /**
     * Activate the specified customer.
     *
     * @param  User $customer
     * @return \Illuminate\Http\Response
     */
    public function activate(User $customer)
    {
        if(!$customer->_isActive()) {
            $customer->activate();
        }
        return redirect(route('customer.list'))->with(['status' => 'success', 'message' => "$customer->name is now active", 'title' => 'OK']);
    }

    /**
     * Delete the specified customer.
     *
     * @param User $customer
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function delete(User $customer)
    {
        $customer->deleteWithRelated();
        return redirect(route('customer.list'))->with(['status' => 'success', 'message' => 'Customer deleted successfully', 'title' => 'OK']);
    }

    public function customersAutocompleteSearch(Request $request)
    {
        $searchVal = $request->get('term');
        $users = User::where('name', $searchVal)->orWhereRaw(searchQueryConstructor($searchVal, ['name', 'email']))
            ->where('users.id', '<>', auth()->id())
            ->orderByRaw(orderByQueryConstructor($searchVal, ['name', 'email']))
            ->limit(100)->get();
        $usersData = CustomerAutocompleteResource::collection($users);
        return response()->json($usersData, 200);
    }
}
