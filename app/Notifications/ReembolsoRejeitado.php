<?php

namespace App\Notifications;

use App\Models\Reembolso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReembolsoRejeitado extends Notification implements ShouldQueue
{
    use Queueable;

    public $reembolso;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reembolso $reembolso)
    {
        $this->reembolso = $reembolso;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Atenção: Correção Necessária no seu Reembolso - Siscoop3F')
                    ->greeting('Olá, ' . explode(' ', $notifiable->name)[0] . '!')
                    ->line('Sua solicitação de reembolso no valor de R$ ' . number_format($this->reembolso->valor_solicitado, 2, ',', '.') . ' foi analisada, contudo identificamos uma inconsistência ou falta de informações.')
                    ->line('Motivo apontado pela Administração: ' . $this->reembolso->motivo_recusa)
                    ->action('Rever Problema no Portal', url('/cooperado/reembolsos'))
                    ->line('Por favor, entre através do botão acima para excluir a solicitação atual e encaminhar uma nova com os anexos corretos.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reembolso_id' => $this->reembolso->id,
            'valor' => $this->reembolso->valor_solicitado,
            'motivo' => $this->reembolso->motivo_recusa,
        ];
    }
}
