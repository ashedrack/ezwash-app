<?php

namespace App\Console\Commands;

use App\Classes\Meta;
use App\Models\Company;
use App\Models\LoyaltyOffer;
use App\Models\PaymentMethod;
use App\Models\SpecialOfferCustomer;
use App\Models\UserDiscountStatus;
use App\Models\UsersDiscount;
use Illuminate\Console\Command;

class ActivateOrDeactivateOffer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loyalty_offers:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate or deactivate loyalty offers on their start or end date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $companies = Company::whereHas('loyaltyOffers')->get();
        foreach($companies as $company){
            $this->deactivateDueOffers($company->id);
            $this->activateDueOffer($company->id);
        }
        $this->activateSpecialOffers();
        return true;
    }

    public function activateDueOffer($company_id)
    {
        $now = now()->format('Y-m-d');
        $offerDueForActivation = LoyaltyOffer::where('company_id', $company_id)
            ->where('is_special_offer', false)
            ->where('status', false)
            ->whereDate('start_date', $now)
            ->whereDate('end_date', '>', $now)
            ->first();
        if(!empty($offerDueForActivation)) {
            //Deactivate all active offers due or not since only one can be active at a time
            LoyaltyOffer::where('company_id', $company_id)->update(['status' => false]);
            $offerDueForActivation->update(['status' => true]);
        }else{
            $this->info('No offer is due for activation');
        }
    }

    public function activateSpecialOffers()
    {
        $now = now()->format('Y-m-d');
        $offersDueForActivation = LoyaltyOffer::where('is_special_offer', true)
            ->whereDate('start_date', $now)
            ->whereDate('end_date', '>', $now)
            ->where('status', false)
            ->get();
        if($offersDueForActivation->isEmpty()){
            $this->error('No special offer is due for activation');
            return false;
        }
        foreach ($offersDueForActivation as $offer){
            $offer->update(['status' => true]);
            $offer->special_offer_customers()->each(function (SpecialOfferCustomer $offerCustomer) {
                $offer = $offerCustomer->loyalty_offer;
                $customer = $offerCustomer->user;
                $latestOrderWithDiscount = $customer->orders()->where('payment_method', PaymentMethod::CARD_PAYMENT)
                    ->where('created_at', '>', $offer->start_date)
                    ->whereHas('discount')->latest()->first();
                $eligibilityStartDate = (!empty($latestOrderWithDiscount)) ? $latestOrderWithDiscount->created_at : $offer->start_date;
                $amountSpentSinceEligible = $customer->orders()->where('status', Meta::ORDER_STATUS_COMPLETED)
                    ->where('payment_method', PaymentMethod::CARD_PAYMENT)
                    ->where('created_at', '>', $eligibilityStartDate)
                    ->sum('amount');
                $offer->users_discounts()->firstOrCreate([
                    'user_id' => $offerCustomer->user_id
                ], [
                    'amount_spent' => $amountSpentSinceEligible,
                    'discount_earned' => $offer->discount_value,
                    'status' => UserDiscountStatus::UNUSED_DISCOUNT
                ]);

            });
        }
        $this->error('Special offers found :: ' . $offersDueForActivation->count());
        return true;
    }

    public function deactivateDueOffers($company_id)
    {
        $now = now()->format('Y-m-d');
        LoyaltyOffer::where('company_id', $company_id)
            ->where('status', true)
            ->where('end_date',  '<=', $now)
            ->update(['status' =>  false]);
    }
}
