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
        return (new MailMessage)
            ->subject('Solicitação de Adesão Recebida com Sucesso')
            ->greeting("Olá, {$this->nome}!")
            ->line('Recebemos sua solicitação de adesão com sucesso.')
            ->line('Seus dados e documentos estão em fase de análise pela nossa equipe administrativa.')
            ->line('Assim que sua solicitação for aprovada, você receberá um novo e-mail contendo suas credenciais e instruções para o primeiro acesso ao portal.')
            ->line('Obrigado pelo seu interesse!');
    }
}
