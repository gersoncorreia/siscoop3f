<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'accepted_lgpd_at',
        'has_temporary_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'accepted_lgpd_at' => 'datetime',
            'password' => 'hashed',
            'has_temporary_password' => 'boolean',
        ];
    }

    /**
     * Get the Associado record linked to this user.
     */
    public function associado()
    {
        return $this->hasOne(Associado::class);
    }

    /**
     * Get the Dependente record linked to this user (if they are a Dependente role).
     */
    public function dependente()
    {
        return $this->hasOne(Dependente::class);
    }
}
