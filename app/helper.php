<?php
use Intervention\Image\ImageManager;
use App\Model\Users;
use App\Model\Settings;
// My common functions

function email_notification($id, $type, $message_body, $subject, $trip = null) {
    $settings = Settings::where('key_setting', 'email_notification')->first();
    $email_notification = $settings->key_value;

    if ($type == 'user') {
        $user = Users::find($id);
        $email = $user->email;
        // dd($email);
    } // elseif ($type == 'admin') {
     //   $user = Admin::find($id);
     //   $email = $user->email;
        //dd($email);
    //}

        if ($trip == 'new_account') {
            Mail::send('emails.new_account', array('mail_body' => $message_body), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
        } else if ($trip == 'has_match') {
            Mail::send('emails.has_match', array('mail_body' => $message_body), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
        } else if ($trip == 'forgotten_pass') {
            Mail::send('emails.forgotten_pass', array('mail_body' => $message_body), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
        }
 
}

function send_push($user_id, $message) {

    require_once 'fcm/FCM_user.php';


    $fcm = new FCMUser();
    
    $user_id = array($user_id);
    $fcm->send_notification($user_id, $message);
    
}

?>