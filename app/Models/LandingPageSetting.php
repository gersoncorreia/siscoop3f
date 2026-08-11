<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageSetting extends Model
{
    protected $fillable = [
        'theme_preset',
        'primary_color',
        'secondary_color',
        'section_hero_enabled',
        'hero_title',
        'hero_subtitle',
        'hero_image_path',
        'logo_path',
        'section_about_enabled',
        'about_title',
        'about_text',
        'section_features_enabled',
        'features',
        'section_contact_enabled',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_facebook',
        'social_instagram',
        'social_linkedin',
    ];

    protected $casts = [
        'section_hero_enabled' => 'boolean',
        'section_about_enabled' => 'boolean',
        'section_features_enabled' => 'boolean',
        'section_contact_enabled' => 'boolean',
        'features' => 'array',
    ];

    protected $appends = [
        'logo_url',
        'hero_image_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? tenant_asset($this->logo_path) : null;
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image_path ? tenant_asset($this->hero_image_path) : null;
    }

    /**
     * Obter os diferenciais/benefícios padrão se estiver vazio.
     */
    public static function getDefaultFeatures(): array
    {
        return [
            [
                'icon' => 'ShieldCheckIcon',
                'title' => 'Segurança e Solidez',
                'description' => 'Garantia de solidez financeira e transparência em todas as operações cooperativas.'
            ],
            [
                'icon' => 'UserGroupIcon',
                'title' => 'Foco no Cooperado',
                'description' => 'Decisões tomadas democraticamente pensando sempre no melhor para a comunidade.'
            ],
            [
                'icon' => 'TrendingUpIcon',
                'title' => 'Retorno Financeiro',
                'description' => 'Distribuição de sobras e taxas mais justas do que no sistema bancário comum.'
            ]
        ];
    }
}
