<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IosAndroidPushNotificationController extends Controller
{
    function sendNotification($id, $title, $body){
        $user=User::find($id, ['device_type', 'device_token']);
        
        if($user['device_type'] == "0"){
           
            return AndroidPush($user['device_token'], $title, $body);
        }
        elseif($user['device_type'] == "1"){
            return IosPush($user['device_token'], $title, $body);
        }
    }
    
    function AndroidPush($deviceToken, $title, $body)
        {
            $firebaseToken = array($deviceToken);
    
            $SERVER_API_KEY = 'AAAAh3dAOUg:APA91bH2agfx4b0XiVVzVgOvAeVB1mCmiKS3ZnY1K5yRXIe9idfIEd92M-6pfmuqPPyx9Wp3sFGkWB72pCmvLVF_7HwNBIa61wtIkEB6-95uZgyv_j7XONySOlA0bOvoi_SCO0uuCGV9';
    
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "content_available" => true,
                    "priority" => "high",
                ]
            ];
            $dataString = json_encode($data);
    
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
    
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    
            $response = curl_exec($ch);
    
            return response($response);
    
            
        }
    
        function IosPush($deviceToken, $title, $body,$mood_icon = '')
        {
    
            $options = [
                'app_bundle_id' => 'com.Skwad-Link.Skwad', // The bundle ID for app obtained from Apple developer account
                'certificate_path' =>storage_path('app/notification_pem/SkwadPush.pem'), // Path to private key
                'certificate_secret' => null // Private key secret
            ];      
            // storage_path('app/notification_pem/bossapp.pem');
            // Be aware of thing that Token will stale after one hour, so you should generate it again.
            // Can be useful when trying to send pushes during long-running tasks
            $authProvider = Certificate::create($options);
            
            $alert = Alert::create()->setTitle('Collabo');
            $alert = $alert->setBody($body);
            //$alert = $alert->setTitleLocKey($type);
            //  $alert = $alert->type($type);
            $payload = Payload::create()->setAlert($alert);
            //set notification sound to default
            $payload->setSound('default');
            //add custom value to your notification, needs to be customized
            //  $payload->setCustomValue('type', $type);
            $payload->setCustomValue('data_payload', array("title"=>$title,"body"=>$body));
            //$payload->setCustomValue('data_payload', $data);
        
            // $payload->setCustomValue('mood_icon', $mood_icon);
            $deviceTokens = [$deviceToken];
            $notifications = [];
            //print_r($payload);die;
            foreach ($deviceTokens as $deviceToken) {
                $notifications[] = new Notification($payload,$deviceToken);
            }
            // If you have issues with ssl-verification, you can temporarily disable it. Please see attached note.
            // Disable ssl verification
             //$client = new Client($authProvider, $production = false, [CURLOPT_SSL_VERIFYPEER=>false] );
            $client = new Client($authProvider, $production = TRUE);
            $client->addNotifications($notifications);
            $responses = $client->push(); // returns an array of ApnsResponseInterface (one Response per Notification)
            if($responses[0]->getStatusCode()==200){
                return sendResponse(200, "Message Sent Successfully", null);
            }
            else{
                return sendResponse(400, "Fail", array($deviceToken,$responses[0]->getErrorReason(),$responses[0]->getErrorDescription()));
            }
    
    
    
            #param sir code
            //echo "failed";
                //return sendResponse(400, "fail",$responses[0]->$responses[0]->getBody(true));
            /*foreach ($responses as $response) {
                // The device token
                //echo $response->getDeviceToken();
                
                // A canonical UUID that is the unique ID for the notification. E.g. 123e4567-e89b-12d3-a456-4266554400a0
                echo $response->getApnsId();
                
                 echo '<br>';
                // Status code. E.g. 200 (Success), 410 (The device token is no longer active for the topic.)
                 echo $response->getStatusCode();
                 
                 echo '<br>';
                // E.g. The device token is no longer active for the topic.
                echo $response->getReasonPhrase();
                echo '<br>';
                // E.g. Unregistered
                echo $response->getErrorReason();
                echo '<br>';
                // E.g. The device token is inactive for the specified topic.
                echo $response->getErrorDescription();
                echo '<br>';
                echo $response->get410Timestamp();
            }
            */
        }
}
