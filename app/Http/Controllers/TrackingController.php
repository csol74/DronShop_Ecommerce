<?php
namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\VueloDron;
use App\Models\SeguimientoOrden;

class TrackingController extends Controller
{
    // Tiempo en segundos por cada paso
    const SEGUNDOS_POR_PASO = 120;

    public function index(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id(), 403);

        if ($orden->estado_entrega === 'entregado') {
            return redirect()->route('orden.show', $orden)
                ->with('success', '✓ Tu pedido ya fue entregado.');
        }

        $orden->load('seguimiento', 'vuelo.dron', 'items.producto');
        return view('tracking.index', compact('orden'));
    }

    public function estado(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);

        // Recargar fresco
        $orden = Orden::with(['seguimiento', 'vuelo'])->find($orden->id);

        // AUTO-AVANCE
        if (!in_array($orden->estado_entrega, ['entregado', 'fallido', 'pendiente_pago'])) {
            $pasoActual = $orden->seguimiento
                ->where('estado', $orden->estado_entrega)
                ->first();

            if ($pasoActual) {
                $referencia       = max(
                    $pasoActual->created_at->timestamp,
                    $pasoActual->updated_at->timestamp
                );
                $segundosEnEstado = now()->timestamp - $referencia;

                if ($segundosEnEstado >= self::SEGUNDOS_POR_PASO) {
                    \App\Http\Controllers\Admin\DronController::procesarAvance($orden);
                    $orden = Orden::with(['seguimiento', 'vuelo'])->find($orden->id);
                }
            }
        }

        $vuelo    = $orden->vuelo;
        $posicion = null;
        $progreso = null; // 0.0 → 1.0 para animación del vehículo

        // Calcular posición interpolada Y progreso para el frontend
        if ($vuelo) {
            if ($vuelo->estado_mision === 'en_vuelo' && $vuelo->hora_despegue) {
                $inicio   = $vuelo->hora_despegue->timestamp;
                $ahora    = now()->timestamp;
                // La duración del movimiento = tiempo del paso "en_camino"
                $duracion = self::SEGUNDOS_POR_PASO;
                $progreso = min(($ahora - $inicio) / $duracion, 1.0);

                $posicion = [
                    'lat' => (float) $vuelo->lat_origen + ((float) $vuelo->lat_destino - (float) $vuelo->lat_origen) * $progreso,
                    'lng' => (float) $vuelo->lng_origen + ((float) $vuelo->lng_destino - (float) $vuelo->lng_origen) * $progreso,
                ];
            } elseif ($vuelo->estado_mision === 'completado') {
                $progreso = 1.0;
                $posicion = [
                    'lat' => (float) $vuelo->lat_destino,
                    'lng' => (float) $vuelo->lng_destino,
                ];
            } else {
                // Programado pero aún no despegó
                $progreso = 0.0;
                $posicion = [
                    'lat' => (float) $vuelo->lat_origen,
                    'lng' => (float) $vuelo->lng_origen,
                ];
            }
        }

        // Calcular progreso del paso actual para la barra de tiempo
        $pasoActualFresh = $orden->seguimiento
            ->where('estado', $orden->estado_entrega)
            ->first();

        $progresoPaso = 0;
        $segundosRestantes = self::SEGUNDOS_POR_PASO;

        if ($pasoActualFresh && !in_array($orden->estado_entrega, ['entregado','fallido'])) {
            $ref              = max($pasoActualFresh->created_at->timestamp, $pasoActualFresh->updated_at->timestamp);
            $transcurridos    = now()->timestamp - $ref;
            $progresoPaso     = min(round(($transcurridos / self::SEGUNDOS_POR_PASO) * 100), 100);
            $segundosRestantes= max(self::SEGUNDOS_POR_PASO - $transcurridos, 0);
        }

        $seguimientoData = $orden->seguimiento->map(fn($s) => [
            'estado'      => $s->estado,
            'titulo'      => $s->titulo,
            'descripcion' => $s->descripcion,
            'icono'       => $s->icono,
            'completado'  => (bool) $s->completado,
            'tiempo'      => $s->completado ? $s->updated_at->format('d/m/Y H:i') : null,
        ]);

        return response()->json([
            'estado_entrega'     => $orden->estado_entrega,
            'estado_orden'       => $orden->estado,
            'entregado'          => in_array($orden->estado_entrega, ['entregado', 'fallido']),
            'seguimiento'        => $seguimientoData,
            'posicion'           => $posicion,
            'progreso_movimiento'=> $progreso,        // 0.0 → 1.0 para el vehículo
            'progreso_paso'      => $progresoPaso,    // 0 → 100 para la barra
            'segundos_restantes' => $segundosRestantes,
            'vuelo'              => $vuelo ? [
                'estado_mision' => $vuelo->estado_mision,
                'lat_origen'    => (float) $vuelo->lat_origen,
                'lng_origen'    => (float) $vuelo->lng_origen,
                'lat_destino'   => (float) $vuelo->lat_destino,
                'lng_destino'   => (float) $vuelo->lng_destino,
            ] : null,
            'transporte'         => $orden->transporte,
        ]);
    }

    public static function iniciarSeguimiento(Orden $orden): void
    {
        if ($orden->seguimiento()->exists()) return;

        $pasos = self::getPasosSegunTransporte($orden->transporte);

        foreach ($pasos as $i => $paso) {
            SeguimientoOrden::create([
                'orden_id'    => $orden->id,
                'estado'      => $paso['estado'],
                'titulo'      => $paso['titulo'],
                'descripcion' => $paso['descripcion'],
                'icono'       => $paso['icono'],
                'completado'  => $i === 0,
                'created_at'  => now(),
                'updated_at'  => $i === 0 ? now() : now()->subYear(),
            ]);
        }

        Orden::where('id', $orden->id)->update([
            'estado_entrega' => $pasos[0]['estado'],
            'estado'         => 'pagado',
        ]);
    }

    public static function getPasosSegunTransporte(string $transporte): array
    {
        $comunes = [
            ['estado'=>'pago_confirmado','titulo'=>'Pago confirmado',  'descripcion'=>'Tu pago fue procesado exitosamente.',        'icono'=>'✅'],
            ['estado'=>'empacando',      'titulo'=>'Preparando pedido','descripcion'=>'El equipo está empacando tus productos.',     'icono'=>'📦'],
            ['estado'=>'recogido',       'titulo'=>'Pedido recogido',  'descripcion'=>'El transportador recogió tu paquete.',        'icono'=>'🏭'],
        ];

        $especificos = match($transporte) {
            'dron' => [
                ['estado'=>'en_camino','titulo'=>'Dron en vuelo',  'descripcion'=>'Tu paquete viaja en nuestro dron DronShop Alpha-1.','icono'=>'🚁'],
                ['estado'=>'cerca',    'titulo'=>'Dron cerca',     'descripcion'=>'El dron está a menos de 500m de tu ubicación.',     'icono'=>'📡'],
                ['estado'=>'entregado','titulo'=>'¡Entregado!',    'descripcion'=>'Tu pedido fue entregado por el dron.',              'icono'=>'🎯'],
            ],
            'moto' => [
                ['estado'=>'en_camino','titulo'=>'Moto en camino', 'descripcion'=>'El mensajero está en camino con tu pedido.',        'icono'=>'🏍️'],
                ['estado'=>'cerca',    'titulo'=>'Mensajero cerca','descripcion'=>'El mensajero está llegando a tu dirección.',        'icono'=>'📍'],
                ['estado'=>'entregado','titulo'=>'¡Entregado!',    'descripcion'=>'Pedido entregado por mensajero en moto.',           'icono'=>'✅'],
            ],
            'carro'=> [
                ['estado'=>'en_camino','titulo'=>'Vehículo en camino','descripcion'=>'El vehículo de reparto está en camino.',         'icono'=>'🚗'],
                ['estado'=>'cerca',    'titulo'=>'Vehículo cerca',    'descripcion'=>'El vehículo está llegando a tu zona.',           'icono'=>'📍'],
                ['estado'=>'entregado','titulo'=>'¡Entregado!',       'descripcion'=>'Pedido entregado por vehículo de reparto.',      'icono'=>'✅'],
            ],
            default => [],
        };

        return array_merge($comunes, $especificos);
    }
}
