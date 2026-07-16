<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TransactionWorkflowNotification extends Notification
{
    public function __construct(
        private readonly array $payload,
        private readonly array $channels = ['database', 'broadcast']
    ) {}

    public function via($notifiable): array
    {
        return $this->channels;
    }

    public function toDatabase($notifiable): array
    {
        return $this->payload;
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }
}
