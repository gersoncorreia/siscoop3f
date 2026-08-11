<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use Spatie\Permission\Models\Role;

class PrestadorSeeder extends Seeder
{
    public function run(): void
    {
        $rolePrestador = Role::firstOrCreate(['name' => 'Prestador', 'guard_name' => 'web']);

        $prestadoresData = [
            [
                'email' => 'clinica.lucas@santacasa.com.br',
                'name' => 'Clínica Médica & Hospital São Lucas',
                'password' => 'prestador123',
                'tipo' => 'clínica',
                'especialidades' => ['Clínica Geral', 'Cardiologia', 'Exames de Imagem'],
                'endereco' => 'Av. Djalma Batista, 1200 - Chapada, Manaus - AM',
                'servicos' => [
                    ['nome' => 'Consulta Clínica Geral', 'descricao' => 'Atendimento médico clínico para adultos', 'valor_acordado' => 120.00],
                    ['nome' => 'Consulta Cardiológica com ECG', 'descricao' => 'Avaliação cardiológica com Eletrocardiograma', 'valor_acordado' => 220.00],
                    ['nome' => 'Ultrassom Abdominal Total', 'descricao' => 'Exame de ultrassonografia da cavidade abdominal', 'valor_acordado' => 180.00],
                    ['nome' => 'Raio-X de Tórax (PA / Perfil)', 'descricao' => 'Radiografia de tórax com laudo', 'valor_acordado' => 95.00],
                    ['nome' => 'Check-up Preventivo', 'descricao' => 'Bateria básica de consultas e exames preventivos', 'valor_acordado' => 350.00],
                ]
            ],
            [
                'email' => 'laboratorio.amazonia@santacasa.com.br',
                'name' => 'Laboratório Diagnósticos da Amazônia',
                'password' => 'prestador123',
                'tipo' => 'laboratório',
                'especialidades' => ['Análises Clínicas', 'Patologia', 'Genética'],
                'endereco' => 'Rua Recife, 450 - Adrianópolis, Manaus - AM',
                'servicos' => [
                    ['nome' => 'Hemograma Completo + Plaquetas', 'descricao' => 'Exame de sangue completo', 'valor_acordado' => 45.00],
                    ['nome' => 'Perfil Lipídico Completo', 'descricao' => 'Colesterol total, HDL, LDL, VLDL e Triglicerídeos', 'valor_acordado' => 65.00],
                    ['nome' => 'Glicemia de Jejum + HbA1c', 'descricao' => 'Controle glicêmico e hemoglobina glicada', 'valor_acordado' => 55.00],
                    ['nome' => 'Exame de Urina Tipo 1 (EAS)', 'descricao' => 'Análise física, química e sedimentoscopia', 'valor_acordado' => 30.00],
                    ['nome' => 'Dosagem de Vitamina D (25-OH)', 'descricao' => 'Quantificação de 25-hidroxivitaminas D', 'valor_acordado' => 90.00],
                ]
            ],
            [
                'email' => 'sorrisoreal@santacasa.com.br',
                'name' => 'Centro Odontológico Sorriso Real',
                'password' => 'prestador123',
                'tipo' => 'consultório',
                'especialidades' => ['Odontologia Geral', 'Ortodontia', 'Endodontia'],
                'endereco' => 'Av. Constelação, 88 - Aleixo, Manaus - AM',
                'servicos' => [
                    ['nome' => 'Limpeza Odontológica + Profilaxia', 'descricao' => 'Remoção de tártaro e polimento coronário', 'valor_acordado' => 150.00],
                    ['nome' => 'Restauração em Resina Fotopolimerizável', 'descricao' => 'Restauração estética de dente posterior/anterior', 'valor_acordado' => 140.00],
                    ['nome' => 'Tratamento de Canal (Endodontia)', 'descricao' => 'Tratamento endodôntico por conduto', 'valor_acordado' => 450.00],
                    ['nome' => 'Radiografia Panorâmica Odontológica', 'descricao' => 'Tomografia / Raio-X panorâmico da arcada', 'valor_acordado' => 100.00],
                ]
            ],
        ];

        foreach ($prestadoresData as $pData) {
            $user = User::updateOrCreate(
                ['email' => $pData['email']],
                [
                    'name' => $pData['name'],
                    'password' => bcrypt($pData['password']),
                    'accepted_lgpd_at' => now(),
                ]
            );

            $user->assignRole($rolePrestador);

            $prestador = Prestador::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nome' => $pData['name'],
                    'tipo' => $pData['tipo'],
                    'especialidades' => $pData['especialidades'],
                    'endereco' => $pData['endereco'],
                ]
            );

            foreach ($pData['servicos'] as $sData) {
                PrestadorServico::updateOrCreate(
                    [
                        'prestador_id' => $prestador->id,
                        'nome' => $sData['nome'],
                    ],
                    [
                        'descricao' => $sData['descricao'],
                        'valor_acordado' => $sData['valor_acordado'],
                        'ativo' => true,
                    ]
                );
            }
        }
    }
}
