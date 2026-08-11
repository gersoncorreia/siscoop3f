<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLandingPageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_preset' => 'required|string|in:emerald,sky,sunset,indigo,custom',
            'primary_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'section_hero_enabled' => 'required|boolean',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:5120',
            'hero_image' => 'nullable|image|max:5120',
            'section_about_enabled' => 'required|boolean',
            'about_title' => 'required|string|max:255',
            'about_text' => 'nullable|string|max:5000',
            'section_features_enabled' => 'required|boolean',
            'features' => 'required|array|min:1|max:6',
            'features.*.icon' => 'required|string|max:50',
            'features.*.title' => 'required|string|max:100',
            'features.*.description' => 'required|string|max:500',
            'section_contact_enabled' => 'required|boolean',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
        ];
    }
}
