<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LgpdSetting extends Model
{
    protected $fillable = [
        'titulo',
        'introducao',
        'secoes',
        'botao_texto',
    ];

    protected $casts = [
        'secoes' => 'array',
    ];

    /**
     * Obter os termos LGPD padrão.
     */
    public static function getDefault(): array
    {
        return [
            'titulo' => 'Termos de Uso e Privacidade (LGPD)',
            'introducao' => 'Para continuar acessando o Siscoop3F e garantir sua segurança, solicitamos que você leia e concorde com nossa política de privacidade em adequação à Lei Geral de Proteção de Dados (LGPD).',
            'secoes' => [
                [
                    'titulo' => '1. Coleta de Dados',
                    'conteudo' => 'O Siscoop3F coleta informações pessoais estritamente necessárias para o gerenciamento das atividades cooperativas/institucionais, como identificação, dados de contato e financeiros das cotas de capital.',
                ],
                [
                    'titulo' => '2. Uso e Compartilhamento',
                    'conteudo' => 'Os dados coletados serão usados exclusivamente pela sua entidade para fins administrativos e de prestação de serviços não sendo compartilhados com terceiros sem consentimento explícito, salvo obrigações legais.',
                ],
                [
                    'titulo' => '3. Seus Direitos',
                    'conteudo' => 'O associado/cliente possui o direito de exigir a alteração e anonimização de dados caso retire-se da organização.',
                ],
            ],
            'botao_texto' => 'Eu Li e Concordo com os Termos',
        ];
    }
}
