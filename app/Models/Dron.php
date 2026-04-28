<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dron extends Model
{
    protected $table = 'drones';
    protected $fillable = [
        'nombre','modelo','numero_serie','fabricante','fecha_adquisicion',
        'autonomia_min','velocidad_max_kmh','alcance_max_km','carga_max_kg',
        'bateria_minima_pct','bateria_actual_pct','zonas_permitidas',
        'condiciones_climaticas','estado','lat_actual','lng_actual',
    ];
    protected $casts = [
        'zonas_permitidas'    => 'array',
        'condiciones_climaticas' => 'array',
        'fecha_adquisicion'   => 'date',
    ];

    public function vuelos()       { return $this->hasMany(VueloDron::class, 'dron_id'); }
    public function mantenimientos(){ return $this->hasMany(Mantenimiento::class, 'dron_id'); }

    public function puedeVolar(float $pesoKg): array
    {
        $errores = [];
        if ($this->estado !== 'disponible')
            $errores[] = "Dron en estado: {$this->estado}";
        if ($pesoKg > $this->carga_max_kg)
            $errores[] = "Peso {$pesoKg}kg supera carga máxima {$this->carga_max_kg}kg";
        if ($this->bateria_actual_pct < $this->bateria_minima_pct)
            $errores[] = "Batería {$this->bateria_actual_pct}% menor al mínimo {$this->bateria_minima_pct}%";
        return $errores;
    }

    public function getEstadoBadgeAttribute(): array
    {
        return match($this->estado) {
            'disponible'    => ['color' => '#4ade80', 'bg' => '#05261640', 'label' => 'Disponible'],
            'en_vuelo'      => ['color' => '#60A5FA', 'bg' => '#0c1a3550', 'label' => 'En vuelo'],
            'mantenimiento' => ['color' => '#F59E0B', 'bg' => '#451a0350', 'label' => 'Mantenimiento'],
            'fuera_servicio'=> ['color' => '#F87171', 'bg' => '#2d0a0a50', 'label' => 'Fuera de servicio'],
            default         => ['color' => '#94A3B8', 'bg' => '#1E2D45',   'label' => $this->estado],
        };
    }
}
