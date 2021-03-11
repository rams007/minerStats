<?php


namespace App\Http\Helpers;


use App\Settings;
use Illuminate\Support\Facades\Auth;
use Mailgun\Mailgun;

class HelperController
{

    public static function sendMail($recepiet, $sender, $subject, $message)
    {

        try {
            $mgClient = Mailgun::create(env('MAILGUN_SECRET'), env('MAILGUN_ENDPOINT'));
            $domain = env('MAILGUN_DOMAIN');
            $params = array(
                'from' => $sender,
                'to' => $recepiet,
                'subject' => $subject,
                'html' => $message
            );

            $result = $mgClient->messages()->send($domain, $params);

            if ($result->getMessage() == 'Queued. Thank you.') {
                return true;
            } else {
                return 'Message not sended.' . $result->getMessage();
            }
        } catch (\Exception $e) {
            return 'Got error: ' . $e->getMessage();
        }

    }

    public static function getEnabledGraphs()
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $allSettingsRecords = Settings::where('user_id', $user->id)->where('parametr_val', 1)->get(['parametr_key']);
        $selectedrecords = [];
        foreach ($allSettingsRecords as $record) {
            $selectedrecords[$record->parametr_key] = 1;
        }

        return $selectedrecords;
    }

}
