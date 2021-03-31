# RahyabSMS notifications channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrhmh/sms-rahyab.svg?style=flat-square)](https://packagist.org/packages/mrhmh/sms-rahyab)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package makes it easy to send notifications using [sms.rahyab.ir](http://sms.rahyab.ir/).

## Installation

Install this package with Composer:

```bash
composer require mrhmh/sms-rahyab
```
### Setting up the Rahyab service

Add your Rahyab credential to `config/services.php`:

```php
// config/services.php
...
'sms_rahyab' => [
        'company'  => env('SMS_RAHYAB_COMPANY'),
        'host'     => env('SMS_RAHYAB_HOST'),
        'port'     => env('SMS_RAHYAB_PORT'),
        'username' => env('SMS_RAHYAB_USERNAME'),
        'password' => env('SMS_RAHYAB_PASSWORD'),
        'sender'   => env('SMS_RAHYAB_SENDER'),
    ],
...
```

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use Illuminate\Notifications\Notification;
use MrHMH\SmsRahyab\SmsRahyabChannel;
use MrHMH\SmsRahyab\SmsRahyabMessage;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [SmsRahyabChannel::class];
    }

    public function toSmsRahyab($notifiable)
    {
        return SmsRahyabMessage::create("Task #{$notifiable->id} is complete!");
    }
}
```

In your notifiable model, make sure to include a `routeNotificationForSmsrahyab()` method, which return a phone number.

```php
public function routeNotificationForSmsrahyab()
{
    return $this->mobile;
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
