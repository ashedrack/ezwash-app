<?php

namespace App\Http\Controllers\CustomerApi;

use App\Classes\Meta;
use App\Models\UserAddress;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Location;
use App\Http\Resources\UserResource;
use App\Http\Resources\LocationResource;


class CustomerController extends Controller
{

    protected $guard = 'users';
    protected $user;

    public function __construct(){

        $this->user = $this->getAuthUser($this->guard);
    }

    public function profile(Request $request)
    {
        try{
            $responseData = new UserResource($this->user);
            return successResponse('Customer Profile', $responseData);
        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function savePlayerId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'player_id' => 'required',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $this->user->update(['notification_player_id' => $request->player_id]);
            return successResponse('Successfully', null, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function locations(Request $request)
    {
        try{
            $locations =  LocationResource::collection(Location::where('is_visible', true)->get());
            $responseData = $locations->toArray($request);
            $responseMessage = "Found {$locations->count()} locations";

            if($locations->count() === 0){
                $responseMessage = "No Locations found";
            }
            return successResponse($responseMessage, $responseData, $request);
        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function updateProfile(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'phone' => ['bail','nullable', new ValidPhone, function ($attribute, $value, $fail)
                    {
                        if($value) {
                            $phone = cleanUpPhone($value);
                            if (User::where('phone', $phone)->where('id', '<>', $this->user->id)->count() > 0) {
                                $fail("The phone number has already been taken");
                            }
                        }
                    }
                ],
                'home_address' => ['nullable','min:5'],
                'latitude' => [
                    Rule::requiredIf(function () use ($request) {
                        return !empty($request->home_address);
                    }),
                    'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'
                ],
                'longitude' => [
                    Rule::requiredIf(function () use ($request) {
                        return !empty($request->home_address);
                    }),
                    'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'
                ],
                'profile_picture' => ['bail', 'nullable', 'active_url'],
                'gender' => ['bail', 'nullable', Rule::in('female','male')],
            ]);

            if ($validator->fails())
            {
                throw new ValidationException($validator);
            }
            if(!$request->hasAny(['phone', 'profile_picture', 'home_address'])){
                return errorResponse('Please specify the field(s) for update', 400, $request );
            }
            $phone = ($request->get('phone')) ? cleanUpPhone($request->phone) : $this->user->phone;
            $avatar = ($request->get('profile_picture')) ? $request->profile_picture : $this->user->avatar;
            User::where('email', $this->user->email)->update([
                'phone' => $phone,
                'avatar' => $avatar,
                'gender' => $request->gender ?? $this->user->gender,
            ]);

            if($request->get('home_address')){

                if($this->user->userAddresses()->where('is_home_address', Meta::IS_HOME_ADDRESS)->count() == 0){

                    UserAddress::create([
                        'user_id' => $this->user->id,
                        'address' => $request->home_address,
                        'longitude' => $request->longitude,
                        'latitude' => $request->latitude,
                        'is_home_address' => Meta::IS_HOME_ADDRESS
                    ]);

                }else{

                    $this->user->userAddresses()->where('is_home_address', Meta::IS_HOME_ADDRESS)->update([
                        'user_id' => $this->user->id,
                        'address' => $request->home_address,
                        'longitude' => $request->longitude,
                        'latitude' => $request->latitude
                    ]);

                }
            }

            $responseData = [
                'customer' => new UserResource(User::find($this->user->id))
            ];
            return successResponse('Customer Updated Successfully', $responseData, $request);

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

    public function updateProfilePicture(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'picture_url' => ['bail', 'required', 'active_url']
            ]);
            if ($validator->fails())
            {
                throw new ValidationException($validator);
            }

            User::where('email', $this->user->email)->update([
                'avatar' => $request->picture_url
            ]);
            $responseData = [
                'customer' => new UserResource(User::find($this->user->id))
            ];
            return successResponse('Profile picture updated successfully', $responseData, $request);

        }catch (ValidationException $e)
        {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);
        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }


}
