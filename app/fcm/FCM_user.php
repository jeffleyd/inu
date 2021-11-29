<?php


class FCMUser {

    //put your code here
    // constructor
    function __construct() {
        
    }

    /**
     * Sending Push Notification
     */
    public function send_notification($registatoin_ids, $message) {
        // include config
        include_once 'const.php';
        /* include_once 'config.php'; */
        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
            'registration_ids' => $registatoin_ids,
            'android_channel_id' => "InuUser",
            'data' => $message,
        );

        $headers = array(
            'Authorization: key=AAAA30SFNJw:APA91bGbhMjoI-wTSsFia6JzOBqKrLh-gHg9kHpO0vSF9n8h3AVtMrsaG_T5zxnZpX-IMkITLPn9aiWuuVMUqqMhZuvwJDXq85P2HaM0C9jxzDwtyy5SM-SkoDIZSFqBH5o2GMtKogic',
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            echo 'false';
            //die('Curl failed: ' . curl_error($ch));
            //Log::error('Curl failed: ' . curl_error($ch));
        }
        else{
            echo $result;
            //Log::error($result);
        }

        // Close connection
        /*curl_close($ch);
         echo $result/*.'

'.json_encode($fields); */
    }

}
?>
