<?php

namespace App\Console\Commands;

use App\Classes\FirebaseConnectionHelper;
use App\Classes\Meta;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Location;
use App\Models\LoyaltyOffer;
use App\Models\Order;
use App\Models\OrdersDiscount;
use App\Models\OrdersService;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserCard;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OldFirebaseDataMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:old_data 
                {--T|table= : Specify the table to import data into E.g transactions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates data from the old firebase project';

    protected $firebaseDataRequest;

    protected $subjectNode;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firebaseDataRequest = new FirebaseConnectionHelper();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->subjectNode = $this->option('table');
        switch ($this->subjectNode){
            case "locations":
                return $this->importLocations();

            case "services":
                return $this->importServices();

            case "employees":
                return $this->importEmployees();

            case "loyalty_offers":
                return $this->importLoyaltyOffers();

            case "users":
                return $this->importUsers();

            case "orders":
                return $this->importOrders();

            default:
                return $this->importLocations();

        }
    }


    public function importLocations()
    {
        $url = '/locations.json';
        try {
            $result = $this->firebaseDataRequest->getDataFromNode($url);
            if($result['status']) {
                $locations = $result['data'];
                foreach ($locations as $nodeID => $location) {
                    $this->info(json_encode(compact('nodeID', 'locations')));
                    Location::firstOrCreate([
                        'prev_firebase_id' => $nodeID
                    ], [
                        'name' => $location['name'],
                        'address' => $location['address'],
                        'phone' => cleanUpPhone($location['phone']),
                        'store_image' => $location['store_image'],
                        'number_of_lockers' => $location['total_lockers'] ?? 0,
                        'company_id' => Company::EZWASH_MAIN,
                        'is_active' => true,
                        'longitude' => null,
                        'latitude' => null,
                    ]);
                }
                logToFileAndDisplay($this, "Locations data imported successfully");
            }else {
                logToFileAndDisplay($this, "Locations data import failed:" . json_encode($result), 'error');
            }
        } catch (\Exception $e){
            $this->error($e->getMessage());
            logCriticalError($url, $e);
        }
    }

    public function importServices()
    {
        $url = "/services.json";
        try {
            $result = $this->firebaseDataRequest->getDataFromNode($url);
            if($result['status']) {
                $services = $result['data'];
                foreach ($services as $nodeID => $service) {
                    $this->info(json_encode(compact('nodeID', 'service')));
                    Service::firstOr(['prev_firebase_id'], function() use ($service) {
                        Service::insert([
                            'name' => $service['name'],
                            'price' => $service['price'],
                            'created_at' => Carbon::createFromTimestamp(($service['created_at']/1000))->toDateTimeString()
                        ]);
                    });
                }
                logToFileAndDisplay($this, "Services data imported successfully");
            }else {
                logToFileAndDisplay($this, "Services data import failed:" . json_encode($result), 'error');
            }
        } catch (\Exception $e){
            $this->error($e->getMessage());
            logCriticalError($url, $e);
        }
    }

    public function importEmployees()
    {
        $url = "/employees.json";
        try {
            $result = $this->firebaseDataRequest->getDataFromNode($url);
            if($result['status']) {
                $employees = $result['data'];
                foreach ($employees as $nodeID => $emp) {
                    $created = $emp['created_at'] ?? isset($emp['modified']) ? $emp['modified'] : null;
                    $this->info(json_encode(compact('nodeID', 'emp')));
                    $location = Location::where('prev_firebase_id', $emp['location'])->first();
                    $employeeToAdd = (object) [
                        'prev_firebase_id' => $nodeID,
                        'email' => $emp['email'],
                        'phone' => cleanUpPhone($emp['phone']),
                        'name' => $emp['name'],
                        'gender' => $emp['gender'],
                        'address' => $emp['address'],
                        'avatar' => $emp['avatar'],
                        'password' => null,
                        'created_by' => null,
                        'location_on_create' => $location ? $location->id : null,
                        'location_id' => $location ? $location->id : null,
                        'company_id' => $location ? $location->company_id : null,
                        'is_active' => true,
                        'created_at' => isset($created) ? Carbon::createFromTimestamp($created/1000) : now()
                    ];
                    if(Employee::where('prev_firebase_id',  $nodeID)->exists()){
                        continue;
                    }
                    if (Employee::where('email', $employeeToAdd->email)->exists()){
                        continue;
                    }
                    if(Employee::where('phone', cleanUpPhone($emp['phone']))->exists()){
                        $fake = factory(Employee::class, 1)->make()->first();
                        $employeeToAdd->phone = generateTestPhoneNumber($fake->phone);
                    }
                    $employeeId = Employee::insertGetId((array)$employeeToAdd);
                    $role = Role::where('name', $emp['role'])->first();
                    EmployeeRole::create([
                        'employee_id' => $employeeId,
                        'role_id' => $role->id
                    ]);
                }
                logToFileAndDisplay($this, "Employees data imported successfully");
            }else {
                logToFileAndDisplay($this, "Employees data import failed:" . json_encode($result), 'error');
            }
        } catch (\Exception $e){
            $this->error($e->getMessage());
            logCriticalError($url, $e);
        }
    }

    public function importLoyaltyOffers()
    {
        $url = '/loyalty_settings.json';
        try {
            $result = $this->firebaseDataRequest->getDataFromNode($url);
            if($result['status']) {
                $loyaltyOffers = $result['data'];
                foreach ($loyaltyOffers as $nodeID => $offer) {
                    $createdBy = isset($offer['created_by']) ? Employee::where('prev_firebase_id', $offer['created_by'])->first(): null;
                    $overallAdmin = getOverallAdmin('victoria@initsng.com');
                    $this->info(json_encode(compact('nodeID', 'offer')));
                    LoyaltyOffer::firstOrCreate([
                        'prev_firebase_id' => $nodeID
                    ], [
                        'company_id' => Company::EZWASH_MAIN,
                        'display_name' => $offer['name'],
                        'spending_requirement' => $offer['spending_requirement'],
                        'discount_value' => $offer['discount_value'],
                        'start_date' => $offer['start_date'],
                        'end_date' => $offer['end_date'],
                        'status' => $offer['status'] ? false: true ,
                        'created_by' => $createdBy ? $createdBy->id: $overallAdmin->id,
                    ]);
                }
                logToFileAndDisplay($this, "Loyalty Offers imported successfully");
            }else {
                logToFileAndDisplay($this, "Loyalty Offers import failed:" . json_encode($result), 'error');
            }
        } catch (\Exception $e){
            $this->error($e->getMessage());
            logCriticalError($url, $e);
        }
    }

    public function importUsers()
    {
        $url = "/users.json";
        try {
            $result = $this->firebaseDataRequest->getDataFromNode($url);
            $processResult = (object)['users_payload' => []];
            if($result['status']) {
                $users = $result['data'];
                foreach ($users as $nodeID => $user) {
                    try {
                        $created = $user['created'] ?? (isset($user['modified']) ? $user['modified'] : null);
                        $createdBy = isset($user['created_by']) ? Employee::where('prev_firebase_id', $user['created_by'])->first() : null;
                        $overallAdmin = getOverallAdmin('victoria@initsng.com');
                        $location = isset($user['location']) ? Location::where('prev_firebase_id', $user['location'])->first(): null;
                        $userInstance = (object)[
                            'prev_firebase_id' => $nodeID,
                            'email' => $user['email'],
                            'phone' => cleanUpPhone($user['phone']),
                            'name' => $user['name'],
                            'gender' => $user['gender'],
                            'avatar' => $user['avatar'] ?? null,
                            'password' => (isset($user['custom_password']) && !$user['custom_password']) ? bcrypt($user['password']) : null,
                            'created_by' => $createdBy ? $createdBy->id : $overallAdmin->id,
                            'location_on_create' => $location ? $location->id : null,
                            'location_id' => $location ? $location->id : null,
                            'is_active' => true,
                            'notification_player_id' => $user['onesignal_player_id'] ?? null,
                            'created_at' => isset($created) ? Carbon::createFromTimestamp($created / 1000) : now(),
                        ];
                        $existingUser = User::where('prev_firebase_id', $nodeID)->first();
                        if (empty($existingUser && User::where('email', $userInstance->email)->exists())) {
                            $existingUser = User::where('email', $userInstance->email)->first();
                        } elseif (empty($existingUser) && User::where('phone', cleanUpPhone($user['phone']))->exists()) {
                            $fake = factory(User::class, 1)->make()->first();
                            $userInstance->phone = generateTestPhoneNumber($fake->phone);
                        }
                        $userID = $existingUser ? $existingUser->id: User::insertGetId((array)$userInstance);
                        if(isset($user['cards']) && $userID) {
                            $this->info('cards :: ' . json_encode($user['cards']));
                            $cardsCreated = $this->addUserCards($userID, $user['cards']);
                            $processResult->users_payload[] = [
                                'id' => $userID, 'firebase_id' => $nodeID, 'cards' => $cardsCreated,
                                "newlyCreated" => (!$existingUser)
                            ];
                        }

                    }catch (\Exception $e){
                        $this->error($e->getMessage());
                        logCriticalError($url, $e);
                        continue;
                    }
                }
                logToFileAndDisplay($this, "Users data imported successfully: Payload - " . json_encode($processResult));
            }else {
                logToFileAndDisplay($this, "Users data import failed:" . json_encode($result), 'error');
            }
        } catch (\Exception $e){
            $this->error($e->getMessage());
            logCriticalError($url, $e);
        }
    }


    public function importOrders()
    {
        $url = "/orders.json";
        $lastImportedId = null;
        if(Setting::where('name', 'firebase_order_last_processed_id')->exists()) {
            $lastImportedId = Setting::where('name', 'firebase_order_last_processed_id')->first()->value;
        }
        if(config('app.CHUNK_ORDERS_IMPORT')){
            $chunkImportSize = config('app.CHUNK_ORDERS_IMPORT_SIZE');
            $url .= '?orderBy="$key"';
            if($lastImportedId){
                $url .= "&startAt=\"{$lastImportedId}\"";
            }
            $url .= "&limitToFirst={$chunkImportSize}";
        }
        try {
            $result = $this->firebaseDataRequest->getDataFromNode($url);
            if($result['status']) {
                $orders = $result['data'];
                $lastProcessed = null;
                foreach ($orders as $nodeID => $order) {
                    try {
                        $created = $order['created'] ?? (isset($order['modified']) ? $order['modified'] : null);
                        $createdBy = isset($order['created_by']) ? Employee::where('prev_firebase_id', $order['created_by'])->first() : null;
                        $overallAdmin = getOverallAdmin('victoria@initsng.com');
                        $location = Location::where('prev_firebase_id', $order['location_id'])->first();
                        $paymentMethods = [
                            'cash' => CASH_PAYMENT,
                            'card' => CARD_PAYMENT,
                            'pos' => POS_PAYMENT,
                            'Customer App' => CARD_PAYMENT,
                            'customer app' => CARD_PAYMENT
                        ];
                        $paymentDate = isset($order['paid_at']) ? $order['paid_at'] : ($order['modified'] ?? null);
                        $user = User::where('prev_firebase_id', $order['user_id'])->first();
                        if(!$user){
                            continue;
                        }

                        $existingOrder = Order::where('prev_firebase_id', $nodeID)->first();
                        $orderInstance = [
                            'user_id' => $user->id,
                            'order_type' => ($order['order_type'] == "self_service") ? Meta::SELF_SERVICE_ORDER_TYPE : Meta::DROP_OFF_ORDER_TYPE,
                            'status' => ($order['status'] == "completed") ? Meta::ORDER_STATUS_COMPLETED : Meta::ORDER_STATUS_PENDING,
                            'amount' => isset($order['discount_id']) ? $order['amount_after_discount'] : $order['amount'],
                            'pickup_cost' => 0,
                            'delivery_cost' => 0,
                            'amount_before_discount' => $order['amount'],
                            'payment_method' => $paymentMethods[strtolower($order['payment_method'])] ?? null,
                            'created_by' => $createdBy ? $createdBy->id : $overallAdmin->id,
                            'location_id' => $location ? $location->id : null,
                            'company_id' => $location ? $location->company_id : null,
                            'collected' => (isset($order['collected']) && $order['collected']),
                            'completed_at' => isset($paymentDate) ? Carbon::createFromTimestamp($paymentDate / 1000) : (isset($created) ? Carbon::createFromTimestamp($created / 1000) : now()),
                            'note' => $order['note'] ?? null,
                            'bags' => $order['bags'] ?? null,
                            'prev_firebase_id' => $nodeID,
                            'created_at' => isset($created) ? Carbon::createFromTimestamp($created / 1000) : now(),
                            'firebase_meta' => json_encode($order),
                        ];
                        if($existingOrder){
                            $existingOrder->update($orderInstance);
                            $orderID = $existingOrder->id;
                        }else{
                            $orderID = Order::insertGetId($orderInstance);
                        }
                        if (isset($order['services'])) {
                            foreach ($order['services'] as $serviceFirebaseID => $servicePayload) {
                                $service = Service::firstOrCreate([
                                    'prev_firebase_id' => $serviceFirebaseID
                                ], [
                                    'name' => $servicePayload['name'] ?? '',
                                    'price' => $servicePayload['price'] ?? $servicePayload['total'] / $servicePayload['quantity'],
                                ]);
                                OrdersService::updateOrCreate([
                                    'order_id' => $orderID,
                                    'service_id' => $service->id,
                                ],[
                                    'quantity' => $servicePayload['quantity'],
                                    'price' => $service->price
                                ]);
                            }
                        }
                        if(isset($order['discount_id'])){
                            $offer = LoyaltyOffer::where('prev_firebase_id', $order['discount_id'])->first();
                            if($offer) {
                                $userDiscount = $user->discounts()->create([
                                    'offer_id' => $offer->id,
                                    'amount_spent' => 0,
                                    'discount_earned' => $order['discount'],
                                    'status' => ($order['status'] == "completed") ? Meta::USED_DISCOUNT: Meta::UNUSED_DISCOUNT
                                ]);
                                OrdersDiscount::updateOrCreate([
                                    'order_id' => $orderID,
                                ],[
                                    'users_discount_id' => $userDiscount->id,
                                    'loyalty_offer_id' => $userDiscount->offer_id
                                ]);
                            }
                        }
                        $lastProcessed = $nodeID;
                    } catch (\Exception $e){
                        $this->error('INLOOP_ERROR : ' . $e->getMessage());
                        logCriticalError($url, $e);
                    }
                }
                if(config('app.CHUNK_ORDERS_IMPORT') && $lastProcessed) {
                    Setting::where('name', 'firebase_order_last_processed_id')->update(['value' => $lastProcessed]);
                }
                logToFileAndDisplay($this, "Order data imported successfully");
            }else {
                logToFileAndDisplay($this, "Order data import failed:" . json_encode($result), 'error');
            }
        } catch (\Exception $e){
            $this->error($e->getMessage() ."::" .  json_encode($result));
            logCriticalError($url, $e);
        }
    }

    function addUserCards($userID, $cardEntries)
    {
        $processedCards = 0;
        foreach ($cardEntries as $ID => $entry){
            if($ID == "undefined" || UserCard::where('prev_firebase_id', $ID)->exists()){
                continue;
            }
            UserCard::firstOrCreate([
                'signature' => $entry['signature']
            ],[
                'user_id' => $userID,
                'auth_code' => $entry['authorization_code'],
                'last_four' => $entry['last4'],
                'card_type' => $entry['card_type'],
                'exp_month' => $entry['exp_month'],
                'exp_year' => $entry['exp_year'],
                'bank' => $entry['bank'],
                'meta' => json_encode($entry)
            ]);
            $processedCards += 1;
        }
        return $processedCards;
    }
}

