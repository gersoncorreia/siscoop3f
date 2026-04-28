<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AppDatabaseNotification extends Notification
{
    private string $title;
    private string $message;
    private ?string $actionUrl;
    private ?string $icon;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $actionUrl = null, ?string $icon = 'BellIcon')
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->icon = $icon;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon,
        ];
    }
}
