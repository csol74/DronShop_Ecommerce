<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $fillable = [
        'dron_id','tipo','descripcion','fecha_programada',
        'fecha_realizada','costo','tecnico','estado','observaciones',
    ];
    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada'  => 'date',
    ];
    public function dron() { return $this->belongsTo(Dron::class, 'dron_id'); }
}
