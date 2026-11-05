<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verify Your Email Address - Grafika South')
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Thank you for creating an account with Grafika South Commercial CMYK Print Shop.')
                ->line('Please click the button below to verify your email address and unlock full access to your customer dashboard.')
                ->action('Verify Email Address', $url)
                ->line('If you did not register for an account with Grafika South, no further action is required.')
                ->salutation("Best regards,\nGrafika South Team\ngrafikasouth69@gmail.com");
        });
    }
}
