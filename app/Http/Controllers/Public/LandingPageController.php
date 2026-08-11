<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use App\Models\LandingPageSetting;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Route;

class LandingPageController extends Controller
{
    /**
     * Exibir a página OnePage pública do tenant.
     */
    public function index()
    {
        // Se a entidade não possui landing page externa ativada, redireciona direto para a tela de login
        if (tenant() && !tenant()->hasLandingPage()) {
            return redirect()->route('login');
        }

        $settings = LandingPageSetting::first();

        // Se não houver configurações, cria uma instância padrão na memória
        if (!$settings) {
            $settings = new LandingPageSetting();
            $settings->features = LandingPageSetting::getDefaultFeatures();
        }

        // Definir defaults para campos nulos
        $settings->hero_subtitle = $settings->hero_subtitle ?? 'Unindo forças, pessoas e propósitos para transformar a nossa comunidade e gerar prosperidade para todos.';
        $settings->about_text = $settings->about_text ?? 'Somos uma cooperativa comprometida com o crescimento sustentável e o bem-estar de nossos cooperados. Acreditamos na força da união e no trabalho conjunto como pilares de transformação socioeconômica.';

        // Se a coluna features for nula, define os padrões
        if (empty($settings->features)) {
            $settings->features = LandingPageSetting::getDefaultFeatures();
        }

        return Inertia::render('LandingPage/Onepage', [
            'settings' => $settings,
            'canLogin' => Route::has('login'),
        ]);
    }
}
