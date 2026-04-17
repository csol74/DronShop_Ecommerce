<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $fillable = ['user_id', 'producto_id', 'cantidad'];
    protected $table = 'carritos';

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->cantidad * $this->producto->precio;
    }
}
