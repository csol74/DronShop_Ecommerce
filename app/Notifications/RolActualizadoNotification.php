<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RolActualizadoNotification extends Notification
{
    use Queueable;

    public function __construct(public $role) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tu rol ha sido actualizado')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Ahora eres: ' . ucfirst($this->role))
            ->action('Ir al sistema', url('/dashboard'))
            ->line('Gracias por usar DronShop');
    }

    public function toArray($notifiable)
    {
        return [
            'mensaje' => 'Tu rol ahora es ' . $this->role
        ];
    }
}
