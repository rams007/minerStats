<?php


namespace App\Http\Helpers;


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
}
