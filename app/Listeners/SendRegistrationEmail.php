<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRegistrationEmail implements ShouldQueue
{
    public $tries = 3;
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
    public function handle(UserRegistered $event): void
    {
        // Mail::to($event->user->email)->send(new \App\Mail\WelcomeMail($event->user));
        // Log a message before dispatching the job
        Log::info('Dispatching registration email job for: ' . $event->user->email);

        // Dispatch the job for sending the email
        Mail::to($event->user->email)->queue(new \App\Mail\WelcomeMail($event->user));
    }
}
