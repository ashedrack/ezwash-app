<?php


namespace App\Classes;


use Illuminate\Support\Facades\Log;

class PaystackRequestHandler
{
    protected $client;
    protected $secret_key;
    protected $paystack_url = 'https://api.paystack.co';
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->secret_key = config('paystack.paystack_secret_key');  //Secret Key

    }
    public function initializeTransaction($data)
    {
        try{
            $url = "/transaction/initialize/";
            return $this->postRequest($url, $data);

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

    }

    public function verifyTransaction($reference)
    {
        try{
            $url = "/transaction/verify/{$reference}";
            $responseDetails = $this->getRequest($url);

            $errorMessage = "Unable to verify payment";
            if(isset($responseDetails['status']) && $responseDetails['status'] === true){
                $data = isset($responseDetails['data']) ? $responseDetails['data'] : null;
                if(!empty($data)){
                    if($data['status'] == "success") {
                        return [
                            'status' => true,
                            'message' => 'Payment Successful',
                            'data' => $data
                        ];
                    } else if($data['status'] == "reversed"){
                        return [
                            'status' => false,
                            'message' => 'Payment Reversed',
                            'data' => $data
                        ];
                    }
                }
                $errorMessage = $data['gateway_response'];
            }
            return [
                'status' => false,
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function paymentWithExistingCard($data){
        try{
            $url = "/transaction/charge_authorization";
            $responseDetails = $this->postRequest($url,$data);

            $errorMessage = "Unable to complete transaction";
            if(isset($responseDetails['status']) && $responseDetails['status'] === true){
                $data = isset($responseDetails['data']) ? $responseDetails['data'] : null;
                if(!empty($data) && $data['status'] === "success") {
                    return [
                        'status' => true,
                        'message' => 'Payment Successful',
                        'data' => $data
                    ];
                }
                $errorMessage = $data['gateway_response'];
            }
            return [
                'status' => false,
                'message' => $errorMessage,
                'notify_user' => true
            ];

        }catch (\Exception $e)
        {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deactivateAuthorization($data)
    {
        try{
            $url = "/customer/deactivate_authorization";
            return $this->postRequest($url, $data);

        }catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function refundTransaction($reference)
    {
        try{
            $url = "/refund";
            $data = ['transaction' => $reference];
            return $this->postRequest($url, $data);
        }catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function postRequest($url, $paymentData)
    {
        $fullUrl = $this->paystack_url . $url;
        $log = saveExternalApiReq([
            'url' => $fullUrl,
            'method' => 'POST_EXTERNAL',
            'data_param' => json_encode($paymentData),
        ]);
        try {
            $get_response = $this->client->post($fullUrl, [
                'json' => $paymentData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secret_key,
                    'Content-Type' => 'application/json'
                ]
            ]);
            $requestResponse = $get_response->getBody()->getContents();
            $log->update([
                'response' => $requestResponse
            ]);
            $responseBody = json_decode($requestResponse, true);

            return $responseBody;
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

    public function getRequest($url)
    {
        $fullUrl = $this->paystack_url . $url;
        $log = saveExternalApiReq([
            'url' => $fullUrl,
            'method' => 'GET_EXTERNAL',
        ]);
        try {
            $get_response = $this->client->get($fullUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secret_key,
                    'Content-Type' => 'application/json'
                ]
            ]);
            $requestResponse = $get_response->getBody()->getContents();
            $log->update([
                'response' => $requestResponse
            ]);
            $responseBody = json_decode($requestResponse, true);
            return $responseBody;
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
