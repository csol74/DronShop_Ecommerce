<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dron;
use App\Models\Orden;
use App\Models\VueloDron;
use Illuminate\Http\Request;

class DronController extends Controller
{
    public function index()
    {
        $dron         = Dron::with(['vuelos.orden', 'mantenimientos'])->first();
        $vuelosActivos = VueloDron::with('orden.user')
            ->where('estado_mision', 'en_vuelo')->get();
        $historialVuelos = VueloDron::with('orden.user')
            ->latest()->limit(10)->get();
        return view('admin.dron.index', compact('dron', 'vuelosActivos', 'historialVuelos'));
    }

    public function editar()
    {
        $dron = Dron::firstOrFail();
        return view('admin.dron.editar', compact('dron'));
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'nombre'             => 'required|string',
            'modelo'             => 'required|string',
            'fabricante'         => 'required|string',
            'fecha_adquisicion'  => 'required|date',
            'autonomia_min'      => 'required|integer|min:1',
            'velocidad_max_kmh'  => 'required|numeric',
            'alcance_max_km'     => 'required|numeric',
            'carga_max_kg'       => 'required|numeric',
            'bateria_minima_pct' => 'required|integer|min:1|max:50',
            'bateria_actual_pct' => 'required|integer|min:0|max:100',
            'estado'             => 'required|in:disponible,en_vuelo,mantenimiento,fuera_servicio',
        ]);
        Dron::first()->update($data);
        return redirect()->route('admin.dron.index')->with('success', 'Datos del dron actualizados.');
    }

    public function monitoreo()
    {
        $dron           = Dron::first();
        $ordenesActivas = Orden::with('user', 'vuelo')
            ->where('estado_entrega', 'en_camino')
            ->where('transporte', 'dron')
            ->get();
        return view('admin.dron.monitoreo', compact('dron', 'ordenesActivas'));
    }

   public static function procesarAvance(Orden $orden): bool
    {
        // Recargar orden fresca de BD
        $orden = Orden::find($orden->id);

        $pasos   = \App\Http\Controllers\TrackingController::getPasosSegunTransporte($orden->transporte);
        $estados = array_column($pasos, 'estado');
        $actual  = $orden->estado_entrega;
        $idx     = array_search($actual, $estados);

        if ($idx === false || $idx >= count($estados) - 1) return false;

        $siguiente = $pasos[$idx + 1];

        \Illuminate\Support\Facades\DB::transaction(function () use ($orden, $actual, $siguiente) {

            // 1. Marcar ACTUAL como completado con timestamp real
            \App\Models\SeguimientoOrden::where('orden_id', $orden->id)
                ->where('estado', $actual)
                ->update([
                    'completado'  => true,
                    'updated_at'  => now(),
                ]);

            // 2. Crear el paso siguiente si no existe, o marcarlo como activo
            $pasoSiguiente = \App\Models\SeguimientoOrden::where('orden_id', $orden->id)
                ->where('estado', $siguiente['estado'])
                ->first();

            if ($pasoSiguiente) {
                $pasoSiguiente->update(['updated_at' => now()]);
            }

            // 3. Actualizar estado de la orden
            \App\Models\Orden::where('id', $orden->id)
                ->update(['estado_entrega' => $siguiente['estado']]);

            // 4. Si es en_despacho también
            if ($siguiente['estado'] === 'en_camino') {
                \App\Models\Orden::where('id', $orden->id)
                    ->update(['estado' => 'en_despacho']);
            }

            // 5. Gestionar vuelo si es dron
            $ordenFresh = Orden::with('items.producto', 'vuelo')->find($orden->id);
            if ($ordenFresh->transporte === 'dron') {
                (new self)->gestionarVueloDron($ordenFresh, $siguiente['estado']);
            }

            // 6. Si entregado
            if ($siguiente['estado'] === 'entregado') {
                \App\Models\Orden::where('id', $orden->id)
                    ->update(['estado' => 'entregado']);

                $dron = \App\Models\Dron::first();
                if ($dron) {
                    $nuevaBateria = max(5, $dron->bateria_actual_pct - rand(8, 18));
                    $dron->update([
                        'estado'             => 'disponible',
                        'bateria_actual_pct' => $nuevaBateria,
                    ]);
                }

                $ordenFresh = Orden::with('vuelo')->find($orden->id);
                if ($ordenFresh->vuelo) {
                    $ordenFresh->vuelo->update([
                        'estado_mision'   => 'completado',
                        'hora_aterrizaje' => now(),
                    ]);
                }
            }
        });

        return true;
    }

   private function gestionarVueloDron(Orden $orden, string $estado): void
    {
        $dron = \App\Models\Dron::first();
        if (!$dron) return;

        if ($estado === 'en_camino' && !$orden->vuelo) {
            $destino = $this->geocodificarDireccion(
                $orden->direccion_entrega,
                $orden->ciudad
            );

            \App\Models\VueloDron::create([
                'dron_id'       => $dron->id,
                'orden_id'      => $orden->id,
                'hora_despegue' => now(),
                'lat_origen'    => 7.1254,
                'lng_origen'    => -73.1198,
                'lat_destino'   => $destino['lat'],
                'lng_destino'   => $destino['lng'],
                'carga_kg'      => $orden->items->sum(
                    fn($i) => (float) ($i->producto->peso_kg ?? 0.3) * $i->cantidad
                ),
                'estado_mision' => 'en_vuelo',
            ]);

            // Actualizar posición actual del dron en la tabla drones
            $dron->update([
                'estado'     => 'en_vuelo',
                'lat_actual' => 7.1254,
                'lng_actual' => -73.1198,
            ]);
        }
    }

    private function geocodificarDireccion(string $direccion, string $ciudad): array
    {
        $key     = config('services.locationiq.key');
        $query   = urlencode("{$direccion}, {$ciudad}, Santander, Colombia");
        $url     = "https://us1.locationiq.com/v1/search?key={$key}&q={$query}&format=json&limit=1&countrycodes=co&accept-language=es";

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(6)
                ->withHeaders(['User-Agent' => 'DronShop/1.0'])
                ->get($url);

            if ($response->ok()) {
                $data = $response->json();

                if (!empty($data[0]['lat'])) {
                    $lat = (float) $data[0]['lat'];
                    $lng = (float) $data[0]['lon'];

                    \Illuminate\Support\Facades\Log::info('LocationIQ OK', [
                        'direccion'    => $direccion,
                        'lat'          => $lat,
                        'lng'          => $lng,
                        'display_name' => $data[0]['display_name'] ?? '',
                    ]);

                    return ['lat' => $lat, 'lng' => $lng];
                }

                \Illuminate\Support\Facades\Log::warning('LocationIQ sin resultados', [
                    'query' => $query,
                    'body'  => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LocationIQ error: ' . $e->getMessage());
        }

        // Fallback: punto aleatorio dentro de Bucaramanga
        return [
            'lat' => 7.1198 + (rand(-30, 30) / 1000),
            'lng' => -73.1227 + (rand(-20, 20) / 1000),
        ];
    }

    public function apiEstado()
    {
        $dron = Dron::first();

        // Si está en vuelo, actualizar lat/lng interpolada en BD
        if ($dron && $dron->estado === 'en_vuelo') {
            $vuelo = VueloDron::where('estado_mision', 'en_vuelo')->latest()->first();
            if ($vuelo && $vuelo->hora_despegue) {
                $inicio   = $vuelo->hora_despegue->timestamp;
                $ahora    = now()->timestamp;
                $duracion = 60;
                $progreso = min(($ahora - $inicio) / $duracion, 1.0);

                $latActual = (float) $vuelo->lat_origen + ((float) $vuelo->lat_destino - (float) $vuelo->lat_origen) * $progreso;
                $lngActual = (float) $vuelo->lng_origen + ((float) $vuelo->lng_destino - (float) $vuelo->lng_origen) * $progreso;

                $dron->update([
                    'lat_actual' => $latActual,
                    'lng_actual' => $lngActual,
                ]);
                $dron->refresh();
            }
        }

        return response()->json([
            'estado'             => $dron->estado,
            'bateria_actual_pct' => $dron->bateria_actual_pct,
            'lat_actual'         => $dron->lat_actual,
            'lng_actual'         => $dron->lng_actual,
            'estado_badge'       => $dron->estado_badge,
        ]);
    }
}
