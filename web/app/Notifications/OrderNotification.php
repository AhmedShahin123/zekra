<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Order;
use App\Models\Email;

class OrderNotification extends Notification
{
    use Queueable;
    public $order;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $orderEmail = Email::where('type', 'order email')->first();
        $greeting = sprintf('Dear %s!', $notifiable->name);

        return (new MailMessage)
                  ->subject($orderEmail->subject . ' #'. $this->order->id)
                  ->line($orderEmail->content)
                  ->line('your order #'.$this->order->id . ' was initiated with total of ' . round($this->order->total - ($this->order->tax + $this->order->fee)) . '$')
                  ->attach(storage_path('app/public/receipts/' . $this->order->id . '/receipt.pdf'))
                  ->greeting($greeting)
                  ->line('Thank you for using Zekra!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
