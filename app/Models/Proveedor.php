<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = ['nombre', 'empresa', 'email', 'telefono', 'pais', 'descripcion', 'logo'];

    protected $table = 'proveedores';

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
