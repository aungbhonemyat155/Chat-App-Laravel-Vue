<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriReqCancel extends Notification
{
    use Queueable;

    public $unreadNotiCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $data)
    {
        //
        $user = User::find($this->data["friendList"]->first_user_id);
        $this->unreadNotiCount = $user->unreadNotifications->count();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            "data" => json_encode($this->data),
            "unreadNotiCount" => $this->unreadNotiCount
        ]);
    }

    public function broadcastType(): string
    {
        return 'broadcast.friReqCancel';
    }
}
