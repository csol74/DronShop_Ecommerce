<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoOrden extends Model
{
    protected $table    = 'seguimiento_orden';
    protected $fillable = [
        'orden_id','estado','titulo','descripcion','icono',
        'completado','lat','lng','foto_entrega','logistica_user_id'
    ];
    protected $casts = [
        'completado' => 'boolean',
        'lat'        => 'float',
        'lng'        => 'float',
    ];

    public function orden()    { return $this->belongsTo(Orden::class); }
    public function logistica(){ return $this->belongsTo(User::class, 'logistica_user_id'); }
}
