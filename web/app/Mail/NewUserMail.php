<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
class NewUserMail extends Mailable
{
    use Queueable, SerializesModels;
    
    private $notifiable;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($notifiable)
    {
        $this->notifiable = $notifiable;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user.register',
            [
                'user' => $this->notifiable
            ]
        );
    }
}