<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification
{
    use Queueable;

    protected $paymentDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
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
        $amount = $this->paymentDetails['amount'] ?? 0;
        $method = $this->paymentDetails['payment_method'] ?? 'payment';
        $receipt = $this->paymentDetails['mpesa_receipt_number'] ?? null;
        $duration = $this->paymentDetails['subscription_duration'] ?? '1 year';

        $mailMessage = (new MailMessage)
            ->subject('Payment Successful - Membership Activated')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your membership payment has been processed successfully.')
            ->line('**Payment Details:**')
            ->line('Amount: KES ' . number_format($amount, 2))
            ->line('Payment Method: ' . ucfirst($method))
            ->line('Subscription Duration: ' . $this->formatDuration($duration));

        if ($receipt) {
            $mailMessage->line('M-Pesa Receipt Number: ' . $receipt);
        }

        $mailMessage->line('Your membership is now active and you have full access to all premium features.')
            ->action('Access Your Account', url('/'))
            ->line('Thank you for joining our platform!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_success',
            'title' => 'Payment Successful',
            'message' => 'Your membership payment of KES ' . number_format($this->paymentDetails['amount'] ?? 0, 2) . ' has been processed successfully.',
            'amount' => $this->paymentDetails['amount'] ?? 0,
            'payment_method' => $this->paymentDetails['payment_method'] ?? null,
            'mpesa_receipt_number' => $this->paymentDetails['mpesa_receipt_number'] ?? null,
            'subscription_duration' => $this->paymentDetails['subscription_duration'] ?? null,
            'action_url' => url('/'),
            'action_text' => 'View Dashboard'
        ];
    }

    /**
     * Format subscription duration for display
     */
    private function formatDuration($duration)
    {
        return match($duration) {
            '3_months' => '3 Months',
            '6_months' => '6 Months', 
            '9_months' => '9 Months',
            '1_year' => '1 Year',
            default => ucfirst(str_replace('_', ' ', $duration))
        };
    }
}