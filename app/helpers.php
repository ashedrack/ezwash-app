<?php

use App\Models\Employee;
use App\Models\ExceptionLog;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Log;

const CARD_PAYMENT = 1;
const CASH_PAYMENT = 2;
const POS_PAYMENT = 3;

const ORDER_STATUS_PENDING = 1;
const ORDER_STATUS_COMPLETED = 2;

function generateTestPhoneNumber($phone){
    $phone = substr(preg_replace("/[^0-9]/", "", $phone), -8);
    $phonePrefix = ['90','70','80','81'];
    return '+234'.$phonePrefix[rand(0,3)] . $phone;
}

/**
 * This function is for test/initial seed purpose only
 * @param $email
 * @return App\Models\Employee $overall_admin
 */
function getOverallAdmin($email)
{
    $overallAdmin = Employee::where('email', $email)->first();

    // Get the overall admin role, create the role is it doesn't exists
    $overallAdminRole = Role::firstOrCreate([
        'name' => 'overall_admin',
        'hierarchy' => 50,
    ], [
        'display_name' => 'Overall Admin',
        'description' => 'Has access to the entire application especially companies creation, modification and deletion'
    ]);
    if (empty($overallAdmin)) {
        // Create an employee
        $overallAdmin = factory(Employee::class, 1)->create([
            'email' => $email,
            'password' => null
        ])->first();

    }
    // Assign that employee the role of overall admin
    if (!$overallAdmin->hasRole('overall_admin')) {
        $overallAdmin->attachRole($overallAdminRole);
    }
    return $overallAdmin;
}

function generateFakeEmployee($params){
    $role = (array_key_exists('role', $params)) ? $params['role'] : [
        'name' => 'overall_admin',
        'hierarchy' => 50,
        'display_name' => 'Overall Admin',
        'description' => 'Has access to the entire application especially companies creation, modification and deletion'
    ];
    $company_id = (array_key_exists('company_id', $params))? $params['company_id'] : null;
    $location_id = (array_key_exists('location_id', $params))? $params['location_id'] : null;
    $email = (array_key_exists('email', $params))? $params['email'] : 'victoria@initsng.com';
    $password = (array_key_exists('password', $params))? $params['password'] : null;
    $mockEmployee = factory(Employee::class)->make();
    $employee = Employee::firstOrCreate(['email' => $email], [
        'company_id' => $company_id,
        'location_id' => $location_id,
        'name' => $mockEmployee->name,
        'phone' => $mockEmployee->phone,
        'password' => $password,
        'is_active' => 1,
        'email_verified_at' => now()
    ]);
    if(!$employee->wasRecentlyCreated) {
        $employee->update([
            'name' => $employee->name,
            'phone' => $employee->phone,
            'password' => $password,
            'is_active' => 1,
            'email_verified_at' => now()
        ]);
    }

    if (!$employee->hasRole($role['name'])) {
        //Get the overall admin role, create the role is it doesn't exists
        $this_role = Role::firstOrCreate([
            'name' => $role['name'],
            'hierarchy' => $role['hierarchy'],
        ], $role);
        $employee->attachRole($this_role);
    }
    return $employee;
}

function developerOnlyAccess(){
    if(!Auth::user()->hasRole('app_developer')){
        return abort(404);
    }
}

function cleanUpPhone($phone)
{
    return (strlen(trim(" " . $phone)) >= 10) ? "+234" . substr(trim($phone), -10) : null;

}

/**
 * Convert json to csv
 *
 * @param string $jsonFilePath
 * @param string $csvFilePath
 * @param null|array $headings
 */
function jsonToCSV($jsonFilePath, $csvFilePath, $headings = null)
{
    if (!($json = file_get_contents($jsonFilePath))) {
        die('Error reading json file...');
    }
    $data = json_decode($json, true);
    $fp = fopen($csvFilePath, 'w');
    $header = $headings;
    foreach ($data as $row)
    {
        if (empty($header))
        {
            $header = ['email'];
            fputcsv($fp, $header);
            $header = array_flip($header);
        }
        fputcsv($fp, array_merge($header, $row));
    }
    fclose($fp);
    return;
}

/**
 * Set an input field to old input in case if a redirect()->withInput()
 * It sets the field the the $otherValue if old input is null
 *
 * @param $oldKey
 * @param $otherValue
 * @return mixed
 */
function oldOrValue($oldKey, $otherValue){
    $oldVal = old($oldKey);
    if(!is_null($oldVal)){
        return $oldVal;
    }
    return $otherValue;
}

function flattenArray($array){
    $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
    $result = [];
    foreach($it as $v) {
        array_push($result, $v);
    }
    return $result;
}

function ddd($var) {
    $dump = $var;
    print_r($dump);
    exit;
}

function save_log(\Illuminate\Http\Request $request, $response) {
    $requestHeaders = json_encode(collect($request->headers)->toArray());
    return \App\Models\ApiLog::create([
        'url' => $request->fullUrl(),
        'method' => $request->method(),
        'request_header' => $requestHeaders,
        'data_param' => json_encode($request->all()),
        'response' => json_encode($response),
        'channel' => ($request->header('channel')) ? $request->header('channel') : $request->get('channel')
    ]);
}

/**
 * save requests to external apis
 *
 * @param $data array
 * @return \App\Models\ApiLog
 */
function saveExternalApiReq($data)
{
    return \App\Models\ApiLog::create($data);
}

function sendDevEmail($message_data, $subject, $from, $to, $type = null) {
    if (!$type) {
        $type = "default";
    }
    $info['message'] = $message_data;
    $info['from'] = $from;
    $info['email'] = $to;
    $info['subject'] = $subject;

    \Illuminate\Support\Facades\Mail::send('email.' . $type, compact('message_data', 'info'), function ($message) use ($info) {
        $message->from("devinfo@ezwashndry.com");
        $message->to($info['email'])->subject($info['subject']);
    });
}

function errorResponse($message = null, $status_code = null, \Illuminate\Http\Request $request = null, Exception $trace = null) {

    $code = ($status_code != null) ? $status_code : 404;
    $responseBody = [
        'message' => "$message",
        'status' => false
    ];
    $errorPayload = null;
    $logBody = null;
    if (!is_null($request)) {
        $logBody = $responseBody;
        $logBody['trace'] = $trace;
        $logBody['status_code'] = $code;
        $savedLog = save_log($request, $logBody);
        $errorPayload = $savedLog->toArray();
    }

    if($trace) {
        logCriticalError($message, $trace, $errorPayload);
    } else {
        $logBody['request_payload'] = $request->all();
        Log::error($message . "\n " . json_encode($logBody));
    }
    return response()->json($responseBody)->setStatusCode("$code");

}

function successResponse($message = null, $data = [], $request = null, $status = true) {
    $body = [
        'message' => "$message",
        'data' => $data,
        'status' => $status, //Would be false if E.g successfully attempted to charge a card but got insufficient fund
    ];

    if (!is_null($request)) {
        $log = $body;
        $log['status_code'] = 200;
        save_log($request, $log);
    }

    return response()->json($body);
}

function logToFileAndDisplay(\Illuminate\Console\Command $commandInstance, $data, $type = 'info'){
    $stringData = (gettype($data) !== 'string') ? json_encode($data) : $data;
    if($type === 'info'){
        $commandInstance->info($data);
        Log::info($stringData);
    } else {
        $commandInstance->error($data);
        Log::error($stringData);
    }
}

/**
 * @param string $errorMessage
 * @param string $errorLine
 * @param string $errorFile
 * @param string $errorTrace
 * @param array | null $payload
 */
function logExceptionToDatabase($errorMessage, $errorLine, $errorFile, $errorTrace, $payload = null){
    $errorLog = ExceptionLog::firstOrCreate([
        'message' => $errorMessage,
        'line' => $errorLine,
        'file' => $errorFile
    ],[
        'url' => $payload ? $payload['url'] ?? null : null,
        'trace_string' => $errorTrace,
        'additional_info' => $payload['additional_info'] ?? null
    ]);
    $errorLog->update([
        'occurrence_count' => $errorLog->occurrence_count + 1
    ]);
}

/**
 * @param $message
 * @param Exception|null $actualException
 * @param null $requestErrorPayload
 */
function logCriticalError($message, Exception $actualException = null, $requestErrorPayload = null)
{
    $errorMessage = $message;
    if($actualException){
        $errorMessage .= "\n
            ActualMessage:: {$actualException->getMessage()} \n
            File:: {$actualException->getFile()} \n
            Line:: {$actualException->getLine()}";
    }
    if(in_array(config('app.env'), ['staging', 'production'])){
        logExceptionToDatabase($errorMessage, $actualException->getLine(), $actualException->getFile(), $actualException->getTraceAsString(), $requestErrorPayload);
        Log::critical($errorMessage);
    }
    Log::error('Message :: ' .$errorMessage . ' |Trace:: ' . ($actualException ? $actualException->getTraceAsString(): ''));
}

function isProductionEnv(){
    return (config('app.env') === 'production');
}

function isStagingEnv(){
    return (config('app.env') === 'staging');
}

function isLocalOrDev($env = null){
    if($env){ return (config('app.env') === $env); }

    return in_array(config('app.env'), ['local', 'develop']);
}

/**
 * Calculate distance between two points A and B in the specified $SIUnit
 *
 * @param $latitudeA
 * @param $longitudeA
 * @param $latitudeB
 * @param $longitudeB
 * @param $SIUnit
 * @return float
 */
function distanceBetweenTwoPoints($latitudeA, $longitudeA, $latitudeB, $longitudeB, $SIUnit) {
    $theta = $longitudeA - $longitudeB;
    $dist = sin(deg2rad($latitudeA)) * sin(deg2rad($latitudeB)) +  cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $SIUnit = strtoupper($SIUnit);

    if ($SIUnit == "K") {
        return ($miles * 1.609344);
    } else if ($SIUnit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function generateUniqueRef($prefix, $uniqueID)
{
    return $prefix . $uniqueID . '.' . strtoupper(uniqid());
}

function sendReportMail($authAdmin, $users, $subject)
{
    try{
        $rows = generateRows($users);
        $columnNames = ["#", ",","Name", ",", "Email", ",", "Phone Number", ",", "Date Created"];
        $csv = createCSV($columnNames, $rows);
        $userCounts = count($users);
        $message_data = "Kindly find the below attached file, it shows the lists of ".$userCounts." ". str_plural('customer', $userCounts);
        \Illuminate\Support\Facades\Mail::send('email.default', compact('users', 'authAdmin', 'message_data'), function ($message) use ($authAdmin, $subject, $csv) {
            $message->from("info@ezwashndry.com");
            $message->to($authAdmin->email)->subject($subject);
            $message->attach($csv);
        });
    }catch(\Exception $e)
    {
        \Illuminate\Support\Facades\Log::error("SendReportMailError =>> ".$e->getMessage());
        return false;
    }

}

function generateRows($users){
    try{
        $rows = [];
        foreach($users as $user) {
            $rows[] = [$user->name, $user->email, $user->phone, \Carbon\Carbon::parse($user->created_at)->format('jS \of F Y')];
        }
        return $rows;
    }catch(\Exception $e)
    {
        \Illuminate\Support\Facades\Log::error("GenerateRowsError =>> ".$e->getMessage());
        return false;
    }

}
function createCSV($columnNames, $rows, $fileName = 'customers_report.csv') {
    try{
        $filePath = 'upload/'.$fileName;
        Storage::disk('public_uploads')->put($filePath, $columnNames);
        foreach ($rows as $key => $row)
        {
            $data = ($key + 1).','.$row[0].','.$row[1].','.$row[2]. ','.$row[3];
            storageAppendInst($filePath, $data);
        }
        $updatedFilePath = public_path('/upload/'.$fileName);
        return $updatedFilePath;
    }catch (\Exception $e)
    {
        \Illuminate\Support\Facades\Log::error("CreateCSVError =>> ".$e->getMessage());
        return false;
    }

}
function storageAppendInst($file_path, $data)
{
    return Storage::disk('public_uploads')->append($file_path, $data);
}

/**
 * Verify the current route by name(s)
 * @param string|array $routeName
 * @return bool
 */
function isCurrentRoute($routeName)
{
    $currentRoute = Request::route()->getName();
    if (is_array($routeName)) {
        return in_array($currentRoute, $routeName);
    }
    return $currentRoute === $routeName;
}

function locationReportStats($startDate, $endDate)
{
    $baseQuery = Transaction::from('transactions as TR')
        ->join('orders', function (JoinClause $join) {
            $join->on('orders.id', 'TR.order_id');
        })
        ->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
        ->where('TR.transaction_status_id', TransactionStatus::COMPLETED)
        ->whereDate('TR.created_at', '>=', $startDate)
        ->whereDate('TR.created_at', '<=', $endDate);

    $orderBaseQueryByPaymentMethodAndLocation = (clone $baseQuery)
        ->select([
            'locations.id as location_id',
            'TR.transaction_payment_method_id as payment_method_id',
            DB::raw('SUM(TR.amount) as income'),
            DB::raw('COUNT(TR.transaction_payment_method_id) as count'),
            //  DB::raw('SUM(orders.pickup_cost + orders.delivery_cost) as pickup_delivery_income'),
        ])->groupBy(['location_id', 'TR.transaction_payment_method_id'])->get();

    return Location::all()->map(function ($location) use ($orderBaseQueryByPaymentMethodAndLocation){

        $orderBaseQueryByPaymentMethod = $orderBaseQueryByPaymentMethodAndLocation->where('location_id', $location->id);

        $total_sales = (clone $orderBaseQueryByPaymentMethod)->sum('income');
        $cashTransactions = (clone $orderBaseQueryByPaymentMethod)->where('payment_method_id', PaymentMethod::CASH_PAYMENT)->first();
        $cardTransactions = (clone $orderBaseQueryByPaymentMethod)->where('payment_method_id', PaymentMethod::CARD_PAYMENT)->first();
        $posTransactions = (clone $orderBaseQueryByPaymentMethod)->where('payment_method_id', PaymentMethod::POS_PAYMENT)->first();

        $resultObject = (object)[];
        //Assign result to object variable.. $resultObject
        $resultObject->location_id = $location->id;
        $resultObject->location_name = $location->name;
        $resultObject->totalSales = $total_sales;
        $resultObject->cashTransactions = $cashTransactions ? $cashTransactions->income : 0;
        $resultObject->cardTransactions = $cardTransactions ? $cardTransactions->income : 0;
        $resultObject->posTransactions = $posTransactions ? $posTransactions->income: 0;

        $resultObject->cardTransactionsCount = $posTransactions ? $posTransactions->count : 0;
        $resultObject->cashTransactionsCount = $posTransactions ? $posTransactions->count : 0;
        $resultObject->posTransactionsCount = $posTransactions ? $posTransactions->count : 0;

        return $resultObject;
    })->toArray();
}

/**
 * @param string $searchString
 * @param array $columns
 * @return string
 */
function searchQueryConstructor($searchString, $columns)
{
    $searchString = htmlentities($searchString);
    $searchWords = array_filter(explode(" ", trim($searchString)));
    if (count($searchWords) === 0) {
        return " 1 ";
    }
    $constructSqlArray = ["("];

    $initial = true;
    foreach ($columns as $columnName) {
        $constructSql = array_map(function ($word) use ($columnName, &$initial) {
            if ($initial) {
                $initial = false;
                return "$columnName LIKE '%{$word}%'";
            }
            return " OR $columnName LIKE '%{$word}%'";
        }, $searchWords);
        $constructSqlArray[] = implode($constructSql);
    }
    return implode($constructSqlArray) . ")";
}

/**
 * @param string $searchString
 * @param array $columns
 * @return string
 */
function orderByQueryConstructor($searchString, $columns)
{
    $searchString = htmlentities($searchString);
    $searchWords = array_filter(explode(" ", trim($searchString)));
    $constructSqlArray = ["(CASE "];

    $orderNum = 1;
    foreach ($columns as $columnName) {

        $query = "WHEN {$columnName} LIKE '{$searchString}' THEN 1";
        $query .= " WHEN {$columnName} LIKE '{$searchString}%' THEN 2";
        $query .= " WHEN {$columnName} LIKE '%{$searchString}' THEN 4 ";

        $constructSqlArray[] = $query;

        $constructSql = array_map(function ($word) use ($columnName, &$orderNum) {
            $q = "WHEN {$columnName} LIKE '{$word}' THEN 1";
            $q .= " WHEN {$columnName} LIKE '{$word}%' THEN 2";
            $q .= " WHEN {$columnName} LIKE '%{$word}' THEN 4 ";
            return $q;
        }, $searchWords);
        $constructSqlArray[] = implode($constructSql);
        $orderNum++;
    }
    return implode($constructSqlArray) . "
            ELSE 3
        END)
    ";
}

/**
 * @param $tableName
 * @return \Doctrine\DBAL\Schema\Index[]
 */
function getTableIndexes($tableName){
    $sm = Schema::getConnection()->getDoctrineSchemaManager();
    return $sm->listTableIndexes($tableName);
}

function isValidPickupAndDeliveryTime($dateTime)
{
    $parsedDate = Carbon::parse($dateTime);
    $startDate = (clone $parsedDate)->setTime(9,0);
    $endDate = (clone $parsedDate)->setTime(17,0);
    if(!$parsedDate->isAfter(now()->addHour())){
        return [
            'status' => false,
            'message' => 'Schedule time must be at least an hour from now'
        ];
    }
    //!$parsedDate->isWeekday() || 
    if(!$parsedDate->between($startDate, $endDate, true)){
        return [
            'status' => false,
            'message' => 'Our pickup and delivery service is only available 9am to 5pm everyday'
        ];
    }

    return ['status' => true, 'message' => 'Valid'];
}

