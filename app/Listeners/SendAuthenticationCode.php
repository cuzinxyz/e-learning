<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Auth\UserCreated;
use Illuminate\Support\Facades\Mail;

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
    }
}
