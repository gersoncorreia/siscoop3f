<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            
            // Design/Cores
            $table->string('theme_preset')->default('emerald'); // emerald, sky, sunset, indigo
            $table->string('primary_color')->default('#004b23');
            $table->string('secondary_color')->default('#ffb703');
            
            // Conteúdo Hero
            $table->boolean('section_hero_enabled')->default(true);
            $table->string('hero_title')->default('Cooperando para um futuro melhor');
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('logo_path')->nullable();
            
            // Conteúdo Sobre
            $table->boolean('section_about_enabled')->default(true);
            $table->string('about_title')->default('Quem Somos');
            $table->text('about_text')->nullable();
            
            // Diferenciais / Benefícios (armazenado em formato JSON para flexibilidade)
            $table->boolean('section_features_enabled')->default(true);
            $table->json('features')->nullable();
            
            // Contatos e Redes Sociais
            $table->boolean('section_contact_enabled')->default(true);
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_linkedin')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
