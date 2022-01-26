<?php


namespace App\Classes;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OneSignalHelper
{
    public static function sendNotificationCustom($userID, $heading, $message, $additionalContent = null)
    {
        if(isLocalOrDev('local')){
            logger("Mock Notification sent to {$userID}");
        }
        try{
            if($userID){
                logger('No player id provided');
            }
            $fields = array(
                'app_id' => config('app.onesignal_app_id'),
                'include_player_ids' => [$userID],
                'headings' => array(
                    "en" => $heading,
                ),
                'contents' => array(
                    "en" => $message,
                ),
                'data' => $additionalContent,
            );

            $url = "https://onesignal.com/api/v1/notifications";
            $get_response = (new Client())->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Basic '. config('app.onesignal_api_key')
                ],
                'json' => $fields
            ]);
            $responseBody = json_decode($get_response->getBody()->getContents(), true);
            if($responseBody['recipients'] > 0) {
                logger('Notification sent successfully :: ' . json_encode($responseBody));
            } else {
                logger('Onesignal notification result :: ' . json_encode($responseBody));
            }

        }catch(\Exception $e){
            logCriticalError('Onesignal notification failed', $e);
        }


    }

}
