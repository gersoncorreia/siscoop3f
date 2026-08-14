<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdesaoRecebidaNotification extends Notification
{
    use Queueable;

    private string $nome;

    public function __construct(string $nome)
    {
        $this->nome = $nome;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tenantName = tenant('nome') ?? 'Fundação Santa Casa da Amazônia';

        return (new MailMessage)
            ->subject('Solicitação de Adesão Recebida com Sucesso')
            ->view('emails.adesao_recebida', [
                'nome' => $this->nome,
                'tenantName' => $tenantName,
            ]);
    }
}
