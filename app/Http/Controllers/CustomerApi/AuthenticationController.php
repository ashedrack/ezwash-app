<?php

namespace App\Http\Controllers\CustomerApi;

use App\Jobs\ProcessUserNotification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'bail|required|email|exists:users,email'
            ]);

            if($validator->fails())
            {
                throw new ValidationException($validator);
            }
            $user = User::where('email', $request->email)->first();

            $userToken = $user->getResetToken();

            Queue::push(new ProcessUserNotification(
                $user,
                '\App\Notifications\CustomerPasswordResetNotification',
                [$userToken]
            ));

            return successResponse('Reset password mail sent!', null, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);
        } catch(\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSetupLink(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'bail|required|email|exists:users,email'
            ]);

            if($validator->fails())
            {
                throw new ValidationException($validator);
            }
            $user = User::where('email', $request->email)->first();

            $userToken = $user->getResetToken();
            Queue::push(new ProcessUserNotification(
                $user,
                '\App\Notifications\CustomerResendPasswordSetupNotification',
                [$userToken]
            ));
            return successResponse('Password setup link has been sent to your email!', null, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);
        } catch(\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }
}
