<?php

// use Illuminate\Support\Facades\Route
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes(['verify' => true]);
Route::get('account/request_setup', 'Auth\SetupAccountController@showSetupRequestForm')->name('account.request_setup');
Route::post('account/email', 'Auth\SetupAccountController@sendSetupLink')->name('account.email');
Route::get('account/setup/{token}', 'Auth\SetupAccountController@showSetupForm')->name('account.setup');
Route::post('account/setup', 'Auth\SetupAccountController@completeSetup')->name('account.complete_setup');
Route::group(['middleware' => 'is_active'], function () {
    Route::get('/', 'AdminController@home')->name('home');
    Route::get('/profile', 'AdminController@profile')->name('admin.profile');
    Route::get('/customers-autocomplete', 'CustomerController@customersAutocompleteSearch')->name('customer.autocomplete_search');

    Route::group(['middleware' => ['permission:create_company|edit_company|list_companies|delete_company']], function () {
        Route::get('/all_companies', 'CompanyController@index')->name('company.list');
        Route::get('/add_company', 'CompanyController@create')->name('company.add');
        Route::post('/save_company', 'CompanyController@save')->name('company.save');
        Route::get('/edit_company/{company}', 'CompanyController@edit')->name('company.edit');
        Route::post('/update_company/{company}', 'CompanyController@update')->name('company.update');
        Route::post('/deactivate_company/{company}', 'CompanyController@deactivate')->name('company.deactivate');
        Route::post('/reactivate_company/{company}', 'CompanyController@activate')->name('company.activate');
        Route::post('/delete_company/{company}', 'CompanyController@delete')->name('company.delete');
        Route::get('/company/{company}', 'CompanyController@view')->name('company.view');
        Route::post('/get-company-locations/{company}', 'CompanyController@getCompanyLocations')->name('company.get_locations');
    });

    Route::group(['middleware' => ['permission:create_location|edit_location|list_locations|deactivate_location|delete_location']], function () {
        Route::get('/all_locations', 'LocationController@index')->name('location.list');
        Route::get('/add_location', 'LocationController@create')->name('location.add');
        Route::post('/save_location', 'LocationController@save')->name('location.save');
        Route::get('/edit_location/{location}', 'LocationController@edit')->name('location.edit');
        Route::post('/update_location/{location}', 'LocationController@update')->name('location.update');
        Route::post('/delete_location/{location}', 'LocationController@delete')->name('location.delete');
        Route::post('/deactivate_location/{location}', 'LocationController@deactivate')->name('location.deactivate');
        Route::post('/reactivate_location/{location}', 'LocationController@activate')->name('location.activate');
        Route::get('/location/{location}', 'LocationController@view')->name('location.view');
    });

    Route::group(['middleware' => ['permission:create_employee|edit_employee|deactivate_employee|delete_employee|list_employees']], function () {
        Route::get('/all_employees', 'EmployeeController@index')->name('employee.list');
        Route::get('/add_employee', 'EmployeeController@create')->name('employee.add');
        Route::post('/save_employee', 'EmployeeController@save')->name('employee.save');
        Route::get('/edit_employee/{employee}', 'EmployeeController@edit')->name('employee.edit');
        Route::post('/update_employee/{employee}', 'EmployeeController@update')->name('employee.update');
        Route::post('/delete_employee/{employee}', 'EmployeeController@delete')->name('employee.delete');
        Route::post('/deactivate_employee/{employee}', 'EmployeeController@deactivate')->name('employee.deactivate');
        Route::post('/reactivate_employee/{employee}', 'EmployeeController@reactivate')->name('employee.activate');
        Route::get('/employee/{employee}', 'EmployeeController@view')->name('employee.view');
    });

    Route::group(['middleware' => ['permission:create_customer|edit_customer|deactivate_customer|delete_customer|list_customers']], function () {
        Route::get('/all_customers', 'CustomerController@index')->name('customer.list');
        Route::get('/add_customer', 'CustomerController@create')->name('customer.add');
        Route::post('/save_customer', 'CustomerController@save')->name('customer.save');
        Route::get('/edit_customer/{customer}', 'CustomerController@edit')->name('customer.edit');
        Route::post('/update_customer/{customer}', 'CustomerController@update')->name('customer.update');
        Route::post('/delete_customer/{customer}', 'CustomerController@delete')->name('customer.delete');
        Route::post('/deactivate_customer/{customer}', 'CustomerController@deactivate')->name('customer.deactivate');
        Route::post('/activate_customer/{customer}', 'CustomerController@activate')->name('customer.activate');
        Route::get('/customer/{customer}', 'CustomerController@view')->name('customer.view');
        Route::post('/filter_customers', 'CustomerController@filter')->name('customer.filter');
        Route::get('/search_customer', 'CustomerController@siteWideSearch')->name('customer.search');
    });

    Route::group(['middleware' => ['permission:create_order|edit_order|delete_order|list_orders']], function () {
        Route::get('/all_orders', 'OrderController@index')->name('order.list');
        Route::get('/add_order/{customer}', 'OrderController@create')->name('order.add');
        Route::post('/save_order', 'OrderController@save')->name('order.save');
        Route::post('/update_order/{order}', 'OrderController@update')->name('order.update');
        Route::post('/delete_order/{order}', 'OrderController@delete')->name('order.delete');
        Route::get('/order/{order}', 'OrderController@view')->name('order.view');
        Route::get('/order_requests', 'OrderRequestsController@index')->name('order_request.list');
        Route::post('/order_request/cancel/{order_request}', 'OrderRequestsController@cancel')->name('orderRequest.cancel');
        Route::get('/order_request/{order_request}', 'OrderRequestsController@view')->name('orderRequest.view');
        Route::post('/order/collect/{order}', 'OrderController@flagOrderAsCollected')->name('order.collect');
    });

    Route::group(['middleware' => ['permission:create_service|edit_service|delete_service|list_services']], function () {
        Route::get('/all_services', 'ServiceController@index')->name('service.list');
        Route::get('/add_service', 'ServiceController@create')->name('service.add');
        Route::post('/save_service', 'ServiceController@save')->name('service.save');
        Route::post('/update_service/{service}', 'ServiceController@update')->name('service.update');
        Route::post('/delete_service/{service}', 'ServiceController@delete')->name('service.delete');
    });

    Route::group([], function () {
        Route::get('/all_loyalty_offers', 'LoyaltyController@index')->name('loyalty_offer.list')->middleware(['permission:list_offers']);
        Route::get('/add_loyalty_offer', 'LoyaltyController@create')->name('loyalty_offer.add')->middleware(['permission:create_offer']);
        Route::post('/save_loyalty_offer', 'LoyaltyController@save')->name('loyalty_offer.save')->middleware(['permission:create_offer']);
        Route::get('/loyalty_offer/{offer}', 'LoyaltyController@view')->name('loyalty_offer.view');
        Route::get('/edit_loyalty_offer/{offer}', 'LoyaltyController@edit')->name('loyalty_offer.edit')->middleware(['permission:edit_offer']);
        Route::post('/update_loyalty_offer/{offer}', 'LoyaltyController@update')->name('loyalty_offer.update')->middleware(['permission:edit_offer']);
        Route::get('/active_loyalty_offer/{company?}', 'LoyaltyController@getActiveOffer')->name('loyalty_offer.get_active');
    });

    Route::group(['middleware' => ['route.permission']], function () {
        Route::get('/special-discount/list', 'LoyaltyController@listSpecialLoyaltyOffers')->name('special_discount.list');
        Route::get('/special-discount/add', 'LoyaltyController@createSpecialOffer')->name('special_discount.create');
        Route::post('/special-discount/save', 'LoyaltyController@saveSpecialOffer')->name('special_discount.save')->middleware('permission:special_discount.create');
        Route::post('/special-discount/update/{offer}', 'LoyaltyController@updateSpecialOffer')->name('special_discount.update')->middleware('permission:special_discount.update');
        Route::get('/special-discount/view/{offer}', 'LoyaltyController@view')->name('special_discount.view');
    });

    Route::get('/general_statistics', 'StatisticsController@index')->name('statistics.general');
    Route::post('/filter_statistics', 'StatisticsController@filter')->name('statistics.filter');
    Route::group(['middleware' => ['permission:list_transactions']], function (){
        Route::get('transactions', 'TransactionsController@index')->name('transactions.index');
        Route::post('transaction/confirm_status', 'TransactionsController@confirmStatus')->name('transaction.confirm_status');
    });

    Route::group(['middleware' => ['permission:view_settings|edit_settings']], function () {
        Route::get('/general_settings', 'SettingsController@index')->name('settings.general');
        Route::get('/edit_settings', 'SettingsController@edit')->name('settings.edit');
        Route::post('/update_settings', 'SettingsController@updateReportRecipients')->name('settings.update_recipients');
        Route::post('/generate_users_list', 'SettingsController@generateUsers')->name('settings.generate_users');
    });

    Route::get('all_notifications', 'NotificationController@index')->name('notification.list');
    Route::post('notifications/read_all', 'NotificationController@markAllAsRead');
    Route::get('notification/mark_as_read/{notification}', 'NotificationController@markAsRead')->name('notification.mark_as_read');
    Route::get('all_permissions', 'AdminController@allPermissions')->name('permission.list');
    Route::post('update_permissions', 'AdminController@updatePermission')->name('permission.update');
    Route::post('save_permission', 'AdminController@savePermission')->name('permission.save');
});

Route::get('customer_account/request_setup', 'CustomerAuth\SetupAccountController@showSetupRequestForm')->name('customer_account.request_setup');
Route::post('customer_account/email', 'CustomerAuth\SetupAccountController@sendSetupLink')->name('customer_account.email');
Route::get('customer_account/setup/{token}', 'CustomerAuth\SetupAccountController@showSetupForm')->name('customer_account.setup');
Route::post('customer_account/setup', 'CustomerAuth\SetupAccountController@completeSetup')->name('customer_account.complete_setup');
Route::get('customer_account/reset_password/{email}/{token}', 'CustomerAuth\ResetPasswordController@showPasswordResetForm')->name('customer_account.reset_password');
Route::post('customer_account/reset_password', 'CustomerAuth\ResetPasswordController@reset')->name('customer.reset_password');
Route::get('/customer/password_reset/success', 'CustomerAuth\ResetPasswordController@resetPasswordSuccessPage')->name('customer.reset_password_success');
Route::get('/customer_api/verify_card', 'PaymentController@verifyCard')->name('add_card.verify');
Route::get('/customer_api/verify_new_card_payment', 'PaymentController@verifyOrderPayment')->name('order_payment.verify_new_card');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
