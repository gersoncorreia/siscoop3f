<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DependenteCriadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $password;
    private string $login;
    private string $nome;
    private string $nomeTitular;
    private string $loginUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $nome, string $login, string $password, string $nomeTitular)
    {
        $this->nome = $nome;
        $this->login = $login;
        $this->password = $password;
        $this->nomeTitular = $nomeTitular;
        // Resolve a URL de login enquanto ainda estamos no contexto da requisição HTTP (tenant ativo)
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
        return (new MailMessage)
            ->subject('Seu Acesso ao Portal da Cooperativa')
            ->greeting("Olá, {$this->nome}!")
            ->line("Você foi cadastrado(a) como dependente de **{$this->nomeTitular}** em nossa cooperativa.")
            ->line('Para acessar o portal pela primeira vez, utilize as seguintes credenciais:')
            ->line("**Seu Login de Acesso (E-mail):** {$this->login}")
            ->line("**Código de Acesso Temporário:** {$this->password}")
            ->action('Acessar o Portal', $this->loginUrl)
            ->line('Por motivos de segurança, você será solicitado a criar uma senha definitiva no seu primeiro acesso.')
            ->line('Bem-vindo(a) à cooperativa!');
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
