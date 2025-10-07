<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Successful')
            ->greeting('Payment Confirmation')
            ->line('Your payment has been successfully processed.')
            ->line('Payment Details:')
            ->line('Amount: ' . $this->paymentDetails['amount'])
            ->line('Transaction ID: ' . $this->paymentDetails['transaction_id'])
            ->line('Date: ' . $this->paymentDetails['date'])
            ->action('View Transactions', url('/transactions'))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_success',
            'amount' => $this->paymentDetails['amount'],
            'transaction_id' => $this->paymentDetails['transaction_id'],
            'date' => $this->paymentDetails['date'],
            'message' => 'Payment of ' . $this->paymentDetails['amount'] . ' processed successfully.'
        ];
    }
}
