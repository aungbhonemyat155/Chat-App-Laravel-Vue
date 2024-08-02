<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriendAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $data)
    {
        //
        $user = User::find($this->data->second_user_id);

        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
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

    public function toDatabase(object $notifiable): array
    {

        return [
            "friend_list_id" => $this->data->id,
            "sender_id" => $this->user->id,
            "sender_name" => $this->user->name,
            "sender_email" => $this->user->email,
            "sender_profile_photo" => $this->user->profile_photo,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            "data" => $this->data->toJson(),
            "senderData" => $this->user
        ]);
    }

    public function databaseType(object $notifiable): string
    {
        return 'FriendAccepted';
    }

    public function broadcastType(): string
    {
        return 'broadcast.friendAccepted';
    }
}
