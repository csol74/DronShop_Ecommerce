<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $fillable = [
        'codigo', 'user_id', 'estado', 'transporte',
        'subtotal', 'costo_envio', 'iva', 'total',
        'direccion_entrega', 'ciudad', 'notas',
        'mp_preference_id', 'mp_payment_id', 'mp_status',
    ];
    protected $table = 'ordenes';

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'iva'         => 'decimal:2',
        'total'       => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrdenItem::class);
    }

    public function getEstadoBadgeAttribute(): array
    {
        return match($this->estado) {
            'pendiente'   => ['label' => 'Pendiente',    'color' => '#F59E0B', 'bg' => '#451a0350'],
            'pagado'      => ['label' => 'Pagado',       'color' => '#34D399', 'bg' => '#05261650'],
            'en_despacho' => ['label' => 'En despacho',  'color' => '#60A5FA', 'bg' => '#0c1a3550'],
            'entregado'   => ['label' => 'Entregado',    'color' => '#A78BFA', 'bg' => '#1e123350'],
            'cancelado'   => ['label' => 'Cancelado',    'color' => '#F87171', 'bg' => '#2d0a0a50'],
            default       => ['label' => $this->estado,  'color' => '#94A3B8', 'bg' => '#1E2D45'],
        };
    }

    public function getTransporteIconAttribute(): string
    {
        return match($this->transporte) {
            'dron' => '🚁',
            'moto' => '🏍️',
            'carro' => '🚗',
            default => '📦',
        };
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::latest()->first();
        $num    = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;
        return 'ORD-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    public function seguimiento()
    {
        return $this->hasMany(\App\Models\SeguimientoOrden::class)->orderBy('created_at');
    }

    public function vuelo()
    {
        return $this->hasOne(\App\Models\VueloDron::class);
    }
}
