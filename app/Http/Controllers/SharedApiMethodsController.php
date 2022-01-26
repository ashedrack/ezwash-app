<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Dingo\Api\Http\Request;

class SharedApiMethodsController extends Controller
{
    public function test(Request $request)
    {
        return successResponse('Welcome to Ezwash Apis - ' . $request->route()->getPrefix(), null);
    }

    public function testV2()
    {
        return successResponse('Welcome to Ezwash Apis - V2', null);
    }

    public function getPaymentMethods(Request $request)
    {
        try {
            $paymentMethods = PaymentMethod::all();
            return successResponse('Successful',['payment_methods' => $paymentMethods], $request);
        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }
}
