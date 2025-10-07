<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        // No parameters needed for welcome notification
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if ($notifiable->email_notifications_enabled ?? true) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Our Platform!')
            ->greeting('Welcome aboard, ' . $notifiable->name . '!')
            ->line('Thank you for joining our platform. We\'re excited to have you as a member!')
            ->line('Here\'s what you can do with your membership:')
            ->line('• Access all premium content and resources')
            ->line('• Download exclusive materials')
            ->line('• Participate in member-only discussions')
            ->line('• Get priority support')
            ->line('Your membership is active until: ' . ($notifiable->subscription_end_date ? $notifiable->subscription_end_date->format('F j, Y') : 'Check your dashboard'))
            ->action('Explore Your Dashboard', url('/'))
            ->line('If you have any questions, don\'t hesitate to reach out to our support team.')
            ->line('Welcome to the community!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'membership_welcome',
            'title' => 'Welcome to Our Platform!',
            'message' => 'Welcome aboard! Your membership is now active. Explore all the premium features available to you.',
            'action_url' => url('/'),
            'action_text' => 'Get Started',
            'membership_expires_at' => $notifiable->subscription_end_date?->format('Y-m-d H:i:s'),
            'days_remaining' => $notifiable->days_until_expiry ?? null
        ];
    }
}