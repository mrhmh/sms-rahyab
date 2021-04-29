<?php


namespace MrHMH\SmsRahyab;


use Illuminate\Notifications\Notification;

class SmsRahyabChannel
{

    protected $sms;

    public function __construct(RESTRahayabSMS $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {

        if (! ($mobileNumber = $this->getMobileNumber($notifiable, $notification)))
            return null;

        $message = $notification->toSmsRahyab($notifiable);
        if (is_string($message))
            $message = new SmsRahyabMessage($message);

        return $this->sendMessage($mobileNumber, $message);
    }


    protected function getMobileNumber($notifiable, Notification $notification): string
    {
        return $notifiable->routeNotificationFor('smsrahyab', $notification);
    }

    protected function sendMessage($mobileNumber, SmsRahyabMessage $message)
    {
        return $this->sms->sendSms($mobileNumber, $message->content);
    }


}
