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
        if (is_null($value)) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true);
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

        $json = json_encode($value);
        return Crypt::encryptString($json);
    }
}
