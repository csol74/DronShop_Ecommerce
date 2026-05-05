<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = ['nombre', 'empresa', 'email', 'telefono', 'pais', 'descripcion', 'logo', 'user_id'];

    protected $table = 'proveedores';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
