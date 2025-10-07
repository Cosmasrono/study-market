<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MembershipExpiryNotification extends Notification
{
    use Queueable;

    /**
     * Notification type (warning or expired)
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new notification instance.
     *
     * @param string $type
     */
    public function __construct(string $type = 'warning')
    {
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage);

        if ($this->type === 'warning') {
            $mailMessage
                ->subject('Your Membership is About to Expire')
                ->greeting("Hello {$notifiable->name},")
                ->line('Your membership is expiring soon.')
                ->line("You have {$notifiable->daysUntilExpiry} days remaining.")
                ->action('Renew Membership', url('/renew'))
                ->line('Renew now to continue enjoying our services!');
        } else {
            $mailMessage
                ->subject('Your Membership Has Expired')
                ->greeting("Hello {$notifiable->name},")
                ->line('Your membership has expired.')
                ->action('Renew Membership', url('/renew'))
                ->line('Reactivate your membership to regain access to our services.');
        }

        return $mailMessage;
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'message' => $this->type === 'warning' 
                ? "Your membership expires in {$notifiable->daysUntilExpiry} days" 
                : 'Your membership has expired',
            'action_url' => url('/renew')
        ];
    }
}
