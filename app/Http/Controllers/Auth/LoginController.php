<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use \Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $recaptcha_site_key = config('app.RECAPTCHA_SITE_KEY');
        return view('auth.login', compact('recaptcha_site_key'));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = Employee::where('email', $request->email)->first();

        if(!empty($user) && $user->password === null){
            return redirect()->route('account.request_setup')->withErrors([
                $this->username() => [
                    trans('auth.setup_required'),
                    'If you did not receive a setup link enter your email below to request a resend']
            ]);
        }
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
            'g-recaptcha-response' => [
                function ($attribute, $value, $fail) use ($request) {
                    if(config('app.ENABLE_RECAPTCHA') === true) {
                        if (!$value) {
                            $fail('Click the recaptcha box to continue');
                        } elseif (!$this->verifyRecaptcha($value, $request->getClientIp())) {
                            $fail('Unable to handle request');
                        }
                    }
                }
            ]
        ]);
    }

    public function verifyRecaptcha($token, $clientIp)
    {
        $guzzle = new Client();
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $verify = $guzzle->post($url, [
            'form_params' => [
                'secret' => config('app.RECAPTCHA_SECRET'),
                'response' => $token,
                'remoteip' => $clientIp
            ]
        ]);
        if($verify->getStatusCode() !== 200){
            return false;
        }
        $responseBody = json_decode($verify->getBody()->getContents());
        if(!$responseBody->success){
            return false;
        }
        return true;
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param   bool $setupRequired
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request, $setupRequired = false)
    {
        if(!$setupRequired) {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }
        throw ValidationException::withMessages([
            $this->username() => [
                trans('auth.setup_required'),
                'If you did not receive a setup link enter your email below to request a resend']
        ]);
    }
}
