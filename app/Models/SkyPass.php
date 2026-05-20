<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SkyPass extends Model
{
    protected $table    = 'skypass';
    protected $fillable = ['user_id','plan','precio_pagado','inicio','vencimiento','activo','mp_payment_id'];
    protected $casts    = [
        'inicio'      => 'datetime',
        'vencimiento' => 'datetime',
        'activo'      => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function estaVigente(): bool
    {
        return $this->activo && $this->vencimiento->isFuture();
    }

    public function diasRestantes(): int
    {
        return max(0, (int) now()->diffInDays($this->vencimiento, false));
    }

    public static function planesDisponibles(): array
    {
        return [
            'mensual'     => ['label' => 'Mensual',     'precio' => 19900,  'meses' => 1,  'ahorro' => null],
            'trimestral'  => ['label' => 'Trimestral',  'precio' => 49900,  'meses' => 3,  'ahorro' => '16%'],
            'anual'       => ['label' => 'Anual',       'precio' => 159900, 'meses' => 12, 'ahorro' => '33%'],
        ];
    }
}
