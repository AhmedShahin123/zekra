<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\NewUserMail as Mailable;
use App\Models\Email;

class NewUserNotification extends Notification
{
    use Queueable;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
          'mail'
          //,'database'
        ];
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $registerEmail = Email::where('type', 'register email')->first();

        $greeting = sprintf($registerEmail->content);

        return (new MailMessage)
                ->subject($registerEmail->subject)
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
        // return [
        //     'username' => $notifiable->username,
        //     'userId' => $notifiable->id,
        //     'email' =>  $notifiable->email,
        //     'created_at' => $notifiable->created_at
        // ];
    }
}
