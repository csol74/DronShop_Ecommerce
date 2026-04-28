<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenItem extends Model
{
    protected $fillable = ['orden_id', 'producto_id', 'nombre_producto', 'precio_unitario', 'cantidad', 'subtotal'];
    protected $table = 'orden_items';
    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    public function seguimiento()
    {
        return $this->hasMany(SeguimientoOrden::class)->orderBy('created_at');
    }
    public function vuelo()
    {
        return $this->hasOne(VueloDron::class);
    }
}
