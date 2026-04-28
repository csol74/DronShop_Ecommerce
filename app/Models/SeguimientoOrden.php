<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoOrden extends Model
{
    protected $table    = 'seguimiento_orden';
    protected $fillable = ['orden_id','estado','titulo','descripcion','icono','completado','lat','lng'];

    protected $casts = [
        'completado' => 'boolean',  // sin esto devuelve "1"/"0" como string
        'lat'        => 'float',
        'lng'        => 'float',
    ];

    public function orden() { return $this->belongsTo(Orden::class); }
}
