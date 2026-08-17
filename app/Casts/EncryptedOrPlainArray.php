<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptedOrPlainArray implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Se $value é nulo (virtual column em Stancl Tenancy), busca de $attributes['data'] ou $model->data
        if (is_null($value)) {
            if (isset($attributes['data'])) {
                $data = is_string($attributes['data']) ? json_decode($attributes['data'], true) : $attributes['data'];
                $value = $data[$key] ?? null;
            } elseif (isset($model->data) && is_array($model->data)) {
                $value = $model->data[$key] ?? null;
            }
        }

        if (is_null($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            $decoded = json_decode($decrypted, true);
            return is_array($decoded) ? $decoded : [];
        } catch (DecryptException $e) {
            // Se falhar a descriptografia, tenta fazer o decode do JSON plano direto do banco
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        $json = json_encode($value);
        return Crypt::encryptString($json);
    }
}
