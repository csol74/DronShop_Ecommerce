<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VueloDron extends Model
{
    protected $table = 'vuelos_dron';
    protected $fillable = [
        'dron_id','orden_id','hora_despegue','hora_aterrizaje',
        'lat_origen','lng_origen','lat_destino','lng_destino',
        'carga_kg','estado_mision','notas',
    ];
    protected $casts = [
        'hora_despegue'   => 'datetime',
        'hora_aterrizaje' => 'datetime',
    ];
    public function dron()  { return $this->belongsTo(Dron::class, 'dron_id'); }
    public function orden() { return $this->belongsTo(Orden::class); }
}
