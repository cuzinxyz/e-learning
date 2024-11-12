<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Auth\UserCreated;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendAuthenticationCode
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        logger()->info('Send authentication code: ' . class_basename($event));

        [
            'email' => $email,
            'subject' => $subject,
            'template' => $template,
            'code' => $code
        ] = $event->data;

        Mail::send(
            $template,
            ['code' => $code],
            function ($message) use ($email, $subject) {
                $message
                    ->to($email)
                    ->subject($subject);
            }
        );

        $text = "A new contact us query\n"
            . "<b>Email Address: </b>\n"
            . "$email\n"
            . "<b>Message: </b>\n"
            . $subject;

        // Telegram
        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }
}
