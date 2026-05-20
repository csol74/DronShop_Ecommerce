<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Campos ocultos al convertir el modelo a Array o JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión automática de tipos de datos (Casting).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Verifica si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica si el usuario es logística.
     */
    public function isLogistica(): bool
    {
        return $this->role === 'logistica';
    }

    /**
     * Verifica si el usuario es proveedor.
     */
    public function isProveedor(): bool
    {
        return $this->role === 'proveedor';
    }

    /**
     * Relación: Un usuario tiene muchas órdenes.
     */
    public function ordenes()
    {
        return $this->hasMany(\App\Models\Orden::class);
    }

    /**
     * Relación: Un usuario puede tener un perfil de proveedor.
     */
    public function proveedor()
    {
        return $this->hasOne(\App\Models\Proveedor::class);
    }

    /**
     * Relación: Obtiene el registro de SkyPass más reciente del usuario.
     */
    public function skypass()
    {
        return $this->hasOne(SkyPass::class)->latest();
    }

    /**
     * Comprueba si el usuario tiene SkyPass y si está activo.
     */
    public function tieneSkyPass(): bool
    {
        $sp = $this->skypass;
        return $sp && $sp->estaVigente();
    }
}
