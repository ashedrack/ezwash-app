<?php

namespace App\Http\Controllers\CustomerAuth;

use Illuminate\Auth\Events\PasswordReset;
use App\Models\PasswordReset as PasswordResetModel;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    public function redirectPath()
    {
        return route('customer.reset_password_success');
    }

    public function showPasswordResetForm(Request $request, $email, $token)
    {
        Auth::logout(); // Logout all sessions
        $request->session()->flush();
        $request->session()->regenerate();
        $pageTitle = 'Reset Password';
        $password_reset = PasswordResetModel::where('email', $email)->first();
        if(!empty($password_reset)) {
            $start_time = strtotime($password_reset->created_at);
            $end_time = strtotime(Carbon::now()->toDateTimeString());
            $difference = $end_time - $start_time;
            $hours = $difference / (60 * 60);
            if ($hours < config('auth.reset_password_timer')){

                return view('auth.customer_account.reset_password', compact('email', 'token', 'pageTitle'));

            }else{
                PasswordResetModel::where('email', $email)->delete();

                $pageTitle = 'Token Expired';
                $salutation = 'Hello';
                $messageLines = [
                    Lang::trans('passwords.token_expired'),
                ];

            }
        } else {
            $pageTitle = 'Token Expired';
            $salutation = 'Hello';
            $messageLines = [
                Lang::trans('passwords.token'),
            ];
        }

        return view('auth.customer_account.guest_notice', compact('messageLines', 'pageTitle', 'salutation'));
    }

    public function setNewPasswordFormRequest(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'token' => 'required',
                'password' => 'required|confirmed|min:8'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }

            $token_decrypted = $this->encrypt_decrypt('decrypt', $request->token);
            $user = User::where('email', $request->email)->first();
            if($user->password == $token_decrypted){
                User::where('email', $request->email)->update([
                    'password' => Hash::make($request->password)
                ]);
                \App\Models\PasswordReset::where('email', $request->email)->delete();
                session(['user_name' => $user->name]);
                return redirect()->to('/customer/password_reset/success');
            }else{
                abort(403);
            }
        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);
        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function resetPasswordSuccessPage()
    {
        $user_name = session('user_name');
        $salutation = "Hello {$user_name}";
        $messageLines = [
            Lang::trans('passwords.customer_reset'),
        ];
        $pageTitle = "Password Reset Successful";
        return view('auth.customer_account.guest_notice', compact('salutation', 'messageLines', 'pageTitle'));
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );
        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword| User  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }


    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('users');
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('users');
    }
}
