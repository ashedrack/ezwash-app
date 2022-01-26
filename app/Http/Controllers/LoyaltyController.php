<?php

namespace App\Http\Controllers;

use App\Classes\Meta;
use App\Classes\OneSignalHelper;
use App\Classes\StatisticsFilters;
use App\Http\Resources\CustomerAutocompleteResource;
use App\Models\Company;
use App\Models\LoyaltyOffer;
use App\Models\SpecialOfferCustomer;
use App\Models\User;
use App\Models\UserDiscountStatus;
use App\Models\UsersDiscount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoyaltyController extends Controller
{
    const SPECIAL_OFFER_SESSION_KEY = 'SPECIAL_DISCOUNT_DETAILS';

    public function __construct()
    {
        $this->middleware('permission:list_offers', ['only' => ['index']]);
        $this->middleware('permission:create_offer', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_offer', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_offer', ['only' => ['deactivate','delete']]);
    }

    /**
     * Display a listing of the loyalty offers.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $authUser = $this->getAuthUser();
        $loyaltyOffers = LoyaltyOffer::getAllowed($authUser);
        if($request->has('company') || $request->has('name')){
            $this->validate($request, [
                'company' => 'nullable|exists:companies,id',
                'name' => 'nullable'
            ]);
            if($request->get('company')){
                $loyaltyOffers = $loyaltyOffers->where('company_id', $request->company);
            }
            if($request->get('name')){
                $loyaltyOffers = $loyaltyOffers->where('display_name', 'LIKE', "%{$request->name}%");
            }

        }
        $loyaltyOffers = $loyaltyOffers->orderBy('start_date', 'DESC')->paginate(20);
        $loyaltyOffers = $loyaltyOffers->appends($request->toArray());
        return view('loyalty_offer.list', compact('loyaltyOffers', 'authUser'));
    }

    /**
     * Get current active in a company
     *
     * @param Request $request
     * @param Company $company
     * @return mixed
     */
    public function getActiveOffer(Request $request, Company $company = null)
    {
        try {
            if($company){
                $company_id = $company->id;
            }else{
                $company_id = $this->getAuthUser()->company_id;
            }

            if($request->get('offer_id')){
                $activeOffer = LoyaltyOffer::where('company_id', $company_id)->where('id', '<>', $request->offer_id)->where('status', true)->first();
            }else {
                $activeOffer = LoyaltyOffer::where('company_id', $company_id)->where('status', true)->first();
            }

            if(!empty($activeOffer)){
                return successResponse("Active offer found", [
                    "offer" => $activeOffer
                ], null, true);
            }else{
                return successResponse("No active offer found", null, null, false);
            }
        } catch (\Exception $e) {
            Log::error('Error while getting active offer, Trace: ' .$e->getTraceAsString());
            return errorResponse('Something went wrong', 500);
        }
    }

    /**
     * Show the form for creating a new loyalty offer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $authUser = $this->getAuthUser();
        $companies = [];
        if($authUser->can('list_companies')){
            $companies = Company::all();
            $minStartDate = date('Y-m-d', strtotime('tomorrow'));
        }else {
            $minStartDate = LoyaltyOffer::getAllowed($authUser)->max('end_date');
            if (strtotime('tomorrow') > strtotime($minStartDate)) {
                $minStartDate = date('Y-m-d', strtotime('tomorrow'));
            }
        }
        return view('loyalty_offer.create', compact('companies', 'authUser', 'minStartDate'));
    }

    /**
     * Save a newly created loyalty offer
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $authUser = $this->getAuthUser();
        $companyID = $authUser->company_id;
        if(!$companyID) {
            if(!$request->get('company')){
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'company' => 'Please select a company to continue'
                    ]);
            }
            $requestCompany = Company::find($request->company);
            if(empty($requestCompany)){
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'company' => 'The selected company is invalid'
                    ]);
            }
            $companyID = $requestCompany->id;
        }

        $this->validate($request, [
            'company' => 'nullable|exists:companies,id',
            'name' => [
                'required',
                Rule::unique('loyalty_offers','display_name')->where('company_id', $companyID)
            ],
            'spending_requirement' => 'required|integer|digits_between:1,10',
            'discount' => 'required|integer|digits_between:1,10',
            'start_date' => 'date|after_or_equal:tomorrow|date_format:Y-m-d',
            'end_date' => 'date|after:start_date|date_format:Y-m-d',
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);
        $responseMessage = "Offer created successfully.";
        $requestStatus = (request('status') == 'active')? 1 : 0;
        if($requestStatus == 1){
            if(request('force_active') === 'on'){
                //deactivate active offers
                LoyaltyOffer::where('company_id', $companyID)
                    ->where('status', true)
                    ->update(['status' => false]);
            }
            else if(LoyaltyOffer::where('company_id', $companyID)->where('status', true)->exists())
            {
                $responseMessage .= " It is inactive because an active offer already exists";
                $requestStatus = 0;
            }
        }
        LoyaltyOffer::create([
            'company_id' => $companyID,
            'display_name' => request('name'),
            'spending_requirement' => request('spending_requirement'),
            'discount_value' => request('discount'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'status' => $requestStatus,
            'created_by' => $authUser->id
        ]);
        return redirect()->route('loyalty_offer.list')->with(['status' => 'success', 'title' => 'OK', 'message' => $responseMessage]);
    }

    /**
     * Display the specified loyalty offer.
     *
     * @param LoyaltyOffer $offer
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(LoyaltyOffer $offer)
    {
        $authUser = $this->getAuthUser();
        if(!$authUser->can('list_companies')){
            $offer = LoyaltyOffer::where('company_id', $authUser->company_id)->where('id', $offer->id)->first();
            if(empty($offer)){
                throw new ModelNotFoundException();
            }
        }
        $unusedDiscounts = $offer->users_discounts()->where('status', Meta::UNUSED_DISCOUNT)->get();
        return view('loyalty_offer.view', compact('offer', 'authUser', 'unusedDiscounts'));
    }

    /**
     * Show the form for editing the specified loyalty offer.
     *
     * @param Request $request
     * @param LoyaltyOffer $offer
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, LoyaltyOffer $offer)
    {
        $pageTitle = 'Edit Special Discount';
        if(strtotime($offer->end_date) <= strtotime(now()->format('Y-m-d'))){
            return redirect()->route('loyalty_offer.view', ['offer' => $offer->id])-> with([
                'status' => 'error',
                'title' => 'Not Allowed',
                'message' => 'Offer is past the end date and cannot be edited'
            ]);
        }
        $authUser = $this->getAuthUser();
        $offerStatus = $offer->status ? 'active': 'inactive';
        if($offer->is_special_offer) {
            $specialOfferCustomers = $offer->special_offer_customers
                ->map(function (SpecialOfferCustomer $cus) use ($request) {
                    $cus->customer_details = (new CustomerAutocompleteResource($cus->user))->toArray($request);
                    return $cus;
                })->pluck('customer_details', 'user_id');
            return view('special_loyalty_offer.edit', compact('offer', 'authUser', 'offerStatus', 'specialOfferCustomers', 'pageTitle'));
        }
        return view('loyalty_offer.edit', compact('offer', 'authUser', 'offerStatus'));
    }

    /**
     * @param Request $request
     * @param LoyaltyOffer $offer
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, LoyaltyOffer $offer)
    {
        $this->validate($request, [
            'name' => [
                'required',
                Rule::unique('loyalty_offers','display_name')
                    ->where('company_id', $offer->company_id)
                    ->where('id', '<>', $offer->id)
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);
        $responseMessage = "Offer updated successfully.";
        $requestStatus = (request('status') == 'active')? 1 : 0;
        if($requestStatus == 1){
            $activeOffers = LoyaltyOffer::where('company_id', $offer->company_id)->where('id', '<>', $offer->id)->where('status', true);
            if($activeOffers->exists()) {
                if (request('force_active') === 'on') {
                    //deactivate active offers
                    $activeOffers->update(['status' => false]);
                } else {
                    $responseMessage .= " It is inactive because an active offer already exists";
                    $requestStatus = 0;
                }
            }
        }
        $offer->update([
            'display_name' => request('name'),
            'status' => $requestStatus
        ]);
        return redirect(route('loyalty_offer.view', ['offer' => $offer->id]))->with(['status' => 'success', 'title' => 'OK', 'message' =>'Offer updated successfully']);
    }

    /**
     * Display a listing of the loyalty offers.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function listSpecialLoyaltyOffers(Request $request)
    {
        $authUser = $this->getAuthUser();
        $pageTitle = 'Special Discounts';
        $loyaltyOffers = LoyaltyOffer::getAllowed($authUser)->where('is_special_offer', true);
        if($request->has('company') || $request->has('name')){
            $this->validate($request, [
                'company' => 'nullable|exists:companies,id',
                'name' => 'nullable'
            ]);
            if($request->get('company')){
                $loyaltyOffers = $loyaltyOffers->where('company_id', $request->company);
            }
            if($request->get('name')){
                $loyaltyOffers = $loyaltyOffers->where('display_name', 'LIKE', "%{$request->name}%");
            }

        }
        $loyaltyOffers = $loyaltyOffers->orderBy('start_date', 'DESC')->paginate(20);
        $loyaltyOffers = $loyaltyOffers->appends($request->toArray());
        return view('special_loyalty_offer.list-special-offers', compact('loyaltyOffers', 'authUser', 'pageTitle'));
    }

    /**
     * Show the form for creating a new special offer.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSpecialOffer()
    {
        $authUser = $this->getAuthUser();
        $pageTitle =  'Create Special Discount';
        $companies = [];
        if(!$authUser->company_id){
            $companies = Company::all();
        }
        $minStartDate = date('Y-m-d', strtotime('tomorrow'));
        return view('special_loyalty_offer.create', compact('companies', 'authUser', 'minStartDate', 'pageTitle'));
    }

    public function saveSpecialOffer(Request $request)
    {
        $authUser = $this->getAuthUser();
        $companyID = $authUser->company_id ?? $request->company;
        if (empty($companyID)) {
            throw ValidationException::withMessages([
                'company' => 'Please select a company to continue'
            ]);
        }
        $this->validate($request, [
            'company' => 'nullable|exists:companies,id',
            'name' => [
                'required',
                Rule::unique('loyalty_offers', 'display_name')->where('company_id', $companyID)
            ],
            'discount' => 'required|integer|digits_between:1,10',
            'start_date' => 'date|after_or_equal:tomorrow|date_format:Y-m-d',
            'end_date' => 'date|after:start_date|date_format:Y-m-d',
            'customers' => 'required|array'
        ]);


        $offer = LoyaltyOffer::create([
            'company_id' => $companyID,
            'display_name' => $request->name,
            'spending_requirement' => null,
            'discount_value' => $request->discount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => false,
            'created_by' => $authUser->id,
            'is_special_offer' => true
        ]);

        foreach ($request->customers as $customerID){
            $offer->special_offer_customers()->firstOrCreate([
                'user_id' => $customerID
            ], [
                'created_by' =>  $authUser->id
            ]);
        }

        return redirect()->route('special_discount.list')
            ->with(['status' => 'success', 'title' => 'OK', 'message' =>'Special Offer created successfully']);
    }

    public function updateSpecialOffer(Request $request, LoyaltyOffer $offer)
    {
        if(!$offer->is_special_offer){
            return $this->update($request, $offer);
        }
        $authUser = $this->getAuthUser();
        $this->validate($request, [
            'name' => [
                'required',
                Rule::unique('loyalty_offers', 'display_name')->where('company_id', $offer->company_id)
                    ->ignore($offer->id)
            ],
            'discount' => 'required|numeric',
            'start_date' => "date|after_or_equal:{$offer->start_date}|date_format:Y-m-d",
            'end_date' => 'date|after:start_date|date_format:Y-m-d',
            'customers' => 'required|array'
        ]);

        $offer->update([
            'display_name' => $request->name,
            'discount_value' => $request->discount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_special_offer' => true
        ]);

        foreach ($request->customers as $customerID){
            $offer->special_offer_customers()->firstOrCreate([
                'user_id' => $customerID
            ], [
                'created_by' =>  $authUser->id
            ]);
        }
        $offer->special_offer_customers()->whereNotIn('user_id', $request->customers)->delete();

        return redirect()->route('special_discount.list')
            ->with(['status' => 'success', 'title' => 'OK', 'message' => 'Special Offer updated successfully']);
    }

}
