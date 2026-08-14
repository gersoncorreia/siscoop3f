<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdesaoAprovadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $password;
    private string $login;
    private string $nome;
    private string $loginUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $nome, string $login, string $password)
    {
        $this->nome = $nome;
        $this->login = $login;
        $this->password = $password;
        // Resolve a URL de login enquanto ainda estamos no contexto da requisição HTTP (tenant ativo)
        // para evitar que caia no domínio central (APP_URL) quando for executado pela fila em background.
        $this->loginUrl = route('login');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tenantName = tenant('nome') ?? 'Fundação Santa Casa da Amazônia';

        return (new MailMessage)
            ->subject('Sua Adesão foi Aprovada - Bem-vindo!')
            ->view('emails.adesao_aprovada', [
                'nome' => $this->nome,
                'login' => $this->login,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
                'tenantName' => $tenantName,
            ]);
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
}
