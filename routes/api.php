<?php

$api = app('Dingo\Api\Routing\Router');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api->version(['v1', 'v2'], ['middleware' => ['throttle:60,1']], function ($api) {
    $api->get('/test', 'App\Http\Controllers\SharedApiMethodsController@test')->middleware(['check.channel']);

    $api->group(['prefix' => 'v1'], function ($api) {
        $api->get('/', 'App\Http\Controllers\SharedApiMethodsController@test');
    });

    $api->group(['prefix' => 'v2'], function ($api) {
        $api->get('/', 'App\Http\Controllers\SharedApiMethodsController@testv2');

        $api->group([
            'namespace' => 'App\Http\Controllers',
        ], function ($api) {
            $api->get('/get-payment-methods', 'SharedApiMethodsController@getPaymentMethods');
            $api->get('/job-status-webhook', 'CustomerApi\PickupAndDeliveryRequestController@statusChangeWebHook');
            $api->post('/payments-webhook', 'CustomerApi\PaymentsController@webhookHandler');
        });

        $api->group([
            'middleware' => ['check.channel'],
            'namespace' => 'App\Http\Controllers\AdminApi',
            'prefix' => 'employee'
        ], function ($api) {
            $api->post('/login', 'AuthenticationController@login');

            $api->group([
                'middleware' => ['assign.guard:admins', 'jwt.verify']
            ], function ($api) {
                $api->post('/test', 'AuthenticationController@testToken');
                $api->post('/create-customer', 'CustomerController@createCustomer');
                $api->post('/search-customers', 'CustomerController@searchCustomer');
                $api->post('/find-customer', 'CustomerController@findSingleCustomer');
                $api->post('/find-pickup-customer', 'CustomerController@findCustomerWithPickupOrderId');
                $api->post('/get-customer-orders', 'CustomerController@getCustomerOrders');
                $api->post('/get-single-order', 'CustomerController@getCustomerOrder');
                $api->post('/get-all-services', 'OrderController@getAllServices');
                $api->post('/create-selfservice-order', 'OrderController@createSelfServiceOrder');
                $api->post('/create-dropoff-order', 'OrderController@createDropOffOrder');
                $api->post('/update-order', 'OrderController@updateOrder');
                $api->get('/all-lockers', 'OrderController@getLocationLockers');
                $api->post('/collect-order', 'OrderController@markOrderAsCollected');

            });
        });
        $api->group([
            'middleware' => ['check.channel'],
            'namespace' => 'App\Http\Controllers\CustomerApi',
            'prefix' => 'customer'
        ], function ($api) {
            $api->post('/register', 'AuthController@register');
            $api->post('/login', 'AuthController@login');
            $api->post('/forgot-password', 'AuthenticationController@forgotPassword');
            $api->post('/request-setup', 'AuthenticationController@sendSetupLink');
            $api->group([
                'middleware' => ['assign.guard:users', 'jwt.verify']

            ], function ($api) {

                $api->get('/all-orders', 'OrderController@viewAllOrders');
                $api->get('/view-order', 'OrderController@viewOrder');
                $api->post('pickup-order', 'OrderController@pickupOrder');
                $api->post('delivery-order', 'OrderController@deliveryOrder');

                $api->get('/all-locations', 'CustomerController@locations');

                $api->get('/view-profile', 'CustomerController@profile');
                $api->post('/update-profile', 'CustomerController@updateProfile');
                $api->post('update-picture', 'CustomerController@updateProfilePicture');
                $api->post('/save-player-id', 'CustomerController@savePlayerId');

                $api->post('/add-card', 'PaymentsController@addCard');
                $api->get('/all-cards', 'PaymentsController@allCards');
                $api->post('/delete-card', 'PaymentsController@deleteCard');

                $api->post('/new-card-pay', 'PaymentsController@payWithNewCard');
                $api->post('/card-payment', 'PaymentsController@payWithExistingCard');

          		$api->post('/get-pickup-estimate', 'PickupAndDeliveryRequestController@getPickupEstimate');
            	$api->post('/confirm-pickup', 'PickupAndDeliveryRequestController@confirmPickupRequest');

                $api->post('/get-delivery-estimate', 'PickupAndDeliveryRequestController@getDeliveryEstimate');
                $api->post('/confirm-delivery', 'PickupAndDeliveryRequestController@confirmDeliveryRequest');

                $api->get('/get-pickups-and-deliveries', 'PickupAndDeliveryRequestController@getAllOrderRequests');
                $api->get('/track-order-request', 'PickupAndDeliveryRequestController@getOrderRequestCurrentLocation');

                $api->post('/cancel-order-request', 'PickupAndDeliveryRequestController@cancelOrderRequest');
//                $api->get('/pickup-and-delivery/status', 'PickupAndDeliveryRequestController@getOrderStatus');

            });

        });
    });
});

