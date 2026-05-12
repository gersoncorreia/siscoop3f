<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrestadorCriadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $password;
    private string $login;
    private string $nome;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $nome, string $login, string $password)
    {
        $this->nome = $nome;
        $this->login = $login;
        $this->password = $password;
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
            ->subject('Seu acesso ao Portal de Prestadores')
            ->greeting("Olá, {$this->nome}!")
            ->line('Seu cadastro como Prestador de Serviço em nossa rede foi concluído.')
            ->line('Para acessar o portal exclusivo para prestadores, utilize as seguintes credenciais:')
            ->line("**Seu Login de Acesso (E-mail):** {$this->login}")
            ->line("**Código de Acesso Temporário:** {$this->password}")
            ->action('Acessar o Portal', route('login'))
            ->line('Por motivos de segurança, você será solicitado a criar uma senha definitiva no seu primeiro acesso.')
            ->line('Agradecemos a parceria!');
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
