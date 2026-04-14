<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre', 'slug', 'descripcion', 'precio', 'stock', 'imagen', 'caracteristicas', 'categoria_id', 'proveedor_id', 'activo'];

    protected $casts = [
        'caracteristicas' => 'array',
        'precio'          => 'decimal:2',
        'activo'          => 'boolean',
    ];

    protected $table = 'productos';

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function getPrecioFormateadoAttribute(): string
    {
        return '$ ' . number_format($this->precio, 0, ',', '.');
    }
}
