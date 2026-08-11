<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\LandingPageSetting;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\UpdateLandingPageSettingsRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageSettingsController extends Controller
{
    /**
     * Exibir formulário de edição das configurações.
     */
    public function edit(): Response
    {
        $settings = LandingPageSetting::first();

        if (!$settings) {
            $settings = LandingPageSetting::create([
                'theme_preset' => 'emerald',
                'primary_color' => '#004b23',
                'secondary_color' => '#ffb703',
                'features' => LandingPageSetting::getDefaultFeatures(),
            ]);
        }

        if (empty($settings->features)) {
            $settings->features = LandingPageSetting::getDefaultFeatures();
            $settings->save();
        }

        return Inertia::render('Configuracoes/LandingPage/Edit', [
            'settings' => $settings,
        ]);
    }

    /**
     * Atualizar configurações da Landing Page.
     */
    public function update(UpdateLandingPageSettingsRequest $request): RedirectResponse
    {
        $settings = LandingPageSetting::firstOrCreate([]);

        $validated = $request->validated();

        // Upload do Logotipo
        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $validated['logo_path'] = $this->optimizeAndStoreImage($request->file('logo'), 'landing/images', 600);
        }

        // Upload do Banner Hero
        if ($request->hasFile('hero_image')) {
            if ($settings->hero_image_path) {
                Storage::disk('public')->delete($settings->hero_image_path);
            }
            $validated['hero_image_path'] = $this->optimizeAndStoreImage($request->file('hero_image'), 'landing/images', 1920);
        }

        // Atualiza a paleta de cores se o preset mudou e não for "custom"
        if ($validated['theme_preset'] !== 'custom' && $validated['theme_preset'] !== $settings->theme_preset) {
            $presets = [
                'emerald' => ['primary' => '#004b23', 'secondary' => '#ffb703'],
                'sky' => ['primary' => '#0284c7', 'secondary' => '#f97316'],
                'sunset' => ['primary' => '#db2777', 'secondary' => '#fbbf24'],
                'indigo' => ['primary' => '#4f46e5', 'secondary' => '#06b6d4'],
            ];
            
            if (isset($presets[$validated['theme_preset']])) {
                $validated['primary_color'] = $presets[$validated['theme_preset']]['primary'];
                $validated['secondary_color'] = $presets[$validated['theme_preset']]['secondary'];
            }
        }

        $settings->update($validated);

        return redirect()->back()->with('success', 'Configurações da Landing Page atualizadas com sucesso!');
    }

    /**
     * Otimiza uma imagem convertendo-a para WebP e redimensionando se necessário.
     */
    private function optimizeAndStoreImage($file, $folder, $maxSize): ?string
    {
        try {
            if (!extension_loaded('gd')) {
                return $file->store($folder, 'public');
            }

            $data = file_get_contents($file->getRealPath());
            $image = imagecreatefromstring($data);
            if (!$image) {
                return $file->store($folder, 'public');
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Redimensiona proporcionalmente caso exceda o limite máximo
            if ($width > $maxSize || $height > $maxSize) {
                if ($width > $height) {
                    $newWidth = $maxSize;
                    $newHeight = (int) ($height * ($maxSize / $width));
                } else {
                    $newHeight = $maxSize;
                    $newWidth = (int) ($width * ($maxSize / $height));
                }

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            // Define o nome de arquivo WebP único
            $filename = uniqid('img_', true) . '.webp';
            $relativeStoragePath = $folder . '/' . $filename;
            
            $disk = Storage::disk('public');
            if (!$disk->exists($folder)) {
                $disk->makeDirectory($folder);
            }
            
            $absolutePath = $disk->path($relativeStoragePath);
            
            // Grava o arquivo WebP com qualidade 80%
            imagewebp($image, $absolutePath, 80);
            imagedestroy($image);

            return $relativeStoragePath;
        } catch (\Throwable $e) {
            // Em caso de qualquer erro, armazena o arquivo original como fallback
            return $file->store($folder, 'public');
        }
    }
}
