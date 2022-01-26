<?php

namespace App\Http\Controllers\CustomerApi;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserAddress;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Notifications\CustomerPasswordResetNotification;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $guard = 'users';
    public function __construct()
    {
        $guard = $this->guard;
        $this->middleware(['assign.guard:'.$guard, 'jwt.auth'])->except(['login','register']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email,deleted_at,NULL',
                'password' => 'required'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }

            //This will still be transformed before it gets
            $user = User::where('email', $request->email)->first();

            //Check if password has been set
            if(is_null($user->password) || !$user->is_active){
                $responseBody = [
                    'message' => is_null($user->password) ? 'Account has not been setup': trans('accounts.deactivated'),
                    'status' => false,
                    'setup_required' => is_null($user->password)
                ];
                $logBody = $responseBody;
                $logBody['status_code'] = 403;
                save_log($request, $responseBody);
                return response()->json($responseBody)->setStatusCode(403);
            }

            //Ensure the credentials are valid
            $token = $this->getAccessToken(request(['email', 'password']));
            if(!$token){
                return errorResponse('Invalid credentials', 401, $request);
            }

            $data = [
                'access_token' => $token,
                'user' => new UserResource($user)
            ];
            //Grant access
            return successResponse('Login Successful', $data, $request);

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = Arr::flatten(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function testToken(){
        return successResponse('Token works Success', []);
    }
    public function register(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'phone' => ['bail','required', new ValidPhone,
                    function ($attribute, $value, $fail) {
                        $phone = cleanUpPhone($value);
                        if(User::where('phone', $phone)->count() > 0){
                            $fail("The phone number has already been taken");
                        }
                    }
                ],
                'gender' => ['nullable', Rule::in(['male', 'female'])],
                'address' => ['bail', 'required'],
                'latitude' => ['bail', 'required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
                'longitude' => ['bail', 'required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            ]);
            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $phone = cleanUpPhone($request->phone);
            User::create([
                'location_id' => Null,
                'location_on_create' => Null,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
            ]);
            $user_id = User::where('email', $request->email)->first()->id;
            UserAddress::create([
                'user_id' => $user_id,
                'address' => $request->address,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'is_home_address' => true
            ]);
            $message = "You have successfully created an account. A mail has been sent to your inbox, please check to activate your account.";
            return successResponse($message, Null, $request);
        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = implode(' ', $errors);
            return errorResponse($message, 400, $request);

        }catch(\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }

    }
    /**
     * Get the access token.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getAccessToken($credentials)
    {
        return auth($this->guard)->attempt($credentials);
    }

}
