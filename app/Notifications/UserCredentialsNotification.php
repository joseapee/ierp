<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $plainPassword,
        public string $loginUrl,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Login Credentials')
            ->greeting("Hello {$notifiable->name},")
            ->line('An account has been created for you. Below are your login credentials:')
            ->line("**Email:** {$notifiable->email}")
            ->line("**Password:** {$this->plainPassword}")
            ->action('Login Now', $this->loginUrl)
            ->line('Please change your password after your first login.')
            ->salutation('Regards, '.config('app.name'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
