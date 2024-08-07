<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SendMessage extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $friendList, public $message, public $user)
    {
        //
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

    public function toDatabase(object $notifiable): array
    {

        return [
            "message_id" => $this->message->id,
            "from_user_id" => $this->message->from_user_id,
            "to_user_id" => $this->message->to_user_id,
            "message" => $this->message->message,
            "created_at" => $this->message->created_at
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'Message';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "message_id" => $this->message->id,
            "from_user_id" => $this->message->from_user_id,
            "to_user_id" => $this->message->to_user_id,
            "message" => $this->message->message,
            "created_at" => $this->message->created_at
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->friendList->toJson(),
            'message' => $this->message->toJson(),
            'senderData' => $this->user->toJson()
        ]);
    }

    public function broadcastType(): string
    {
        return 'broadcast.sendMessage';
    }
}
