<?php

namespace App\Notifications;

use App\Models\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentoRejeitado extends Notification implements ShouldQueue
{
    use Queueable;

    public $documento;

    /**
     * Create a new notification instance.
     */
    public function __construct(Documento $documento)
    {
        $this->documento = $documento;
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
                    ->subject('Correção Necessária: ' . $this->documento->titulo)
                    ->greeting('Olá, ' . explode(' ', $notifiable->name)[0] . '!')
                    ->line('A administração analisou o documento "' . $this->documento->titulo . '" e não pôde aprová-lo devido a algumas pendências.')
                    ->line('Motivo da Rejeição: ' . $this->documento->motivo_rejeicao)
                    ->action('Acessar Meu Portal para Corrigir', url('/cooperado/documentos'))
                    ->line('Por favor, acesse a sessão de documentos via botão acima, delete o documento recusado e anexe o arquivo correto, para darmos seguimento!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'documento_id' => $this->documento->id,
            'titulo' => $this->documento->titulo,
            'motivo' => $this->documento->motivo_rejeicao,
        ];
    }
}
