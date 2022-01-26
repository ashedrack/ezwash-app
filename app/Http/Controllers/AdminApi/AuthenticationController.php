<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Resources\EmployeeResource;
use App\Http\Resources\LocationResource;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected $guard = 'admins';
    public function __construct()
    {
          $this->middleware(['assign.guard:admins', 'jwt.auth'])->except('login');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'bail|required|email|exists:employees,email,deleted_at,NULL',
                'password' => 'required'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }

            $admin = Employee::where('email', $request->email)->first();

            //Check if password has been set
            if(is_null($admin->password)){
                return errorResponse('Account has not been setup: check you mail for setup instructions', 403, $request);
            }

            //Check if admin is active
            if(!$admin->is_active){
                return errorResponse(trans('accounts.deactivated'), 403, $request);
            }

            //Ensure the credentials are valid
            $token = $this->getAccessToken(request(['email', 'password']));

            if(!$token){
                return errorResponse('Invalid credentials', 401, $request);
            }
            //Ensure employee's account, the company, and the location are active
            if(!$admin->_isActive()){
                return errorResponse('Account is inactive, please contact admin', 403, $request);
            }

            $responseData = [
                'access_token' => $token,
                'employee' => new EmployeeResource($admin)
            ];

            //return a list of location for the admin to select if the admin is not tied to a location
            if(!$admin->location_id || $admin->hasRole('dropoff_admin')){
                $responseData['locations'] = LocationResource::collection(Location::allowedToAccess($admin)->where('is_visible', true)->get());
            }
            //Grant access;
            return successResponse('Login Successful', $responseData, $request);

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function testToken(){
        return successResponse('Token works Success', []);
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
