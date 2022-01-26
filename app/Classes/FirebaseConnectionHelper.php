<?php

namespace App\Classes;

use App\Models\Setting;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Google_Client;

class FirebaseConnectionHelper {

    protected $baseUrl;
    protected $client;
    protected $idToken;

    public function __construct() {
        $this->idToken = config('app.FIREBASE_AUTH_TOKEN');
        $this->baseUrl = isLocalOrDev() ? "https://ezwash-staging.firebaseio.com": "https://ezwash-11.firebaseio.com";
        $this->client = new Client();
    }

    public static function getAccessToken()
    {
        try{
            $gClient = new Google_Client();
            $gClient->setAuthConfig('firebase_service_account_key.json');
            $gClient->setScopes([
                "https://www.googleapis.com/auth/userinfo.email",
                "https://www.googleapis.com/auth/firebase.database"
            ]);
            $result = (object)$gClient->fetchAccessTokenWithAssertion();
            $result->expiry_time = Carbon::createFromTimestamp($result->created)->addSeconds($result->expires_in)->toDateTimeString();
            $accessToken = $result->access_token;
            Setting::updateOrCreate([
                'name' => 'firebase_last_generated_token',
            ], [
                'value' => $accessToken,
                'additional_payload' => (array) $result,
            ])->toArray();
            return $accessToken;
        } catch (\Google_Exception $e) {
            logCriticalError('Access taken generation failed', $e);
        } catch (\Exception $e){
            logCriticalError('Unable to generate firebase access token', $e);
        }
        return null;
    }

    /**
     * @param $nodeUrl
     * @return array
     * @throws \Exception
     */
    public function getDataFromNode($nodeUrl)
    {
        return $this->sendGetRequest($nodeUrl);
    }

    public function sendGetRequest($url, $query = null)
    {
        $fullUrl = $this->baseUrl . $url;
        $log = saveExternalApiReq([
            'url' => $fullUrl,
            'method' => 'GET_EXTERNAL',
        ]);
        $headers = [
            'Content-Type' => 'application/json',
        ];
        try {
            if(isProductionEnv()){
                $accessToken = self::getAccessToken();
                if(!$accessToken){
                    throw new \Exception('No access Token');
                }
                $headers['Authorization'] = 'Bearer '. $accessToken;
            }
            $get_response = $this->client->get($fullUrl, [
                'headers' => $headers,
                'query' => $query
            ]);
            $requestResponse = $get_response->getBody()->getContents();
            $log->update([
                'response' => $requestResponse
            ]);
            $responseBody = json_decode($requestResponse, true);
            if($get_response->getStatusCode() === 200){
                return [
                    'status' => true,
                    'data' => $responseBody,
                    'message' => "Successful",
                ];
            }
            return [
                'status' => false,
                'message' => $responseBody,
                'show_error' => false
            ];
        } catch (GuzzleException $e) {
            $message = "Error while sending a get request to {$fullUrl}";
            logCriticalError($message, $e);
            $log->update([
                'response' => json_encode([
                    'message' => $message . ' ActualMessage:: ' . $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ])
            ]);
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'show_error' => true
            ];
        } catch (\Exception $e){
            $log->update([
                'response' => $e->getMessage()
            ]);
            Log::error(json_encode($e));
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function sendPostRequest($url, $requestData)
    {
        $fullUrl = $this->baseUrl . $url;
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $log = saveExternalApiReq([
            'url' => $fullUrl,
            'method' => 'POST_EXTERNAL',
            'data_param' => json_encode($requestData),
        ]);
        try {
            if(isProductionEnv()){
                $accessToken = self::getAccessToken();
                if(!$accessToken){
                    throw new \Exception('No access Token');
                }
                $headers['Authorization'] = 'Bearer '. $accessToken;
            }
            ddd($headers);
            $log->update(['headers' => json_encode($headers)]);
            $get_response = $this->client->post($fullUrl, [
                'headers' => $headers,
                'json' => $requestData
            ]);
            $requestResponse = $get_response->getBody()->getContents();
            $log->update([
                'response' => $requestResponse
            ]);
            Log::info($requestResponse);
            $responseBody = json_decode($requestResponse, true);
            if($responseBody['status'] === 200){
                return [
                    'status' => true,
                    'data' => $responseBody['data'],
                    'message' => $responseBody['message'],
                ];
            }
            return [
                'status' => false,
                'message' => $responseBody['message'],
                'show_error' => ($responseBody['status'] === 201)
            ];
        }catch (\Exception $e){
            $log->update([
                'response' => $e->getMessage()
            ]);
            Log::error(json_encode($e));
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}