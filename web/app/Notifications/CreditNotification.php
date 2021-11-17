<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Package;
use App\Models\Email;

class CreditNotification extends Notification
{
    use Queueable;
    public $package;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
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
        $creditEmail = Email::where('type', 'credit email')->first();

        $greeting = sprintf('Dear %s!', $notifiable->name);

        return (new MailMessage)
                ->subject($creditEmail->subject)
                ->line($creditEmail->content)
                ->line('You earned new credit with points '.$this->package->credit_points . ' and will expire at ' .$this->package->expire_at->toDateString())
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
