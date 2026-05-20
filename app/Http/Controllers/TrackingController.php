<?php
namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\SeguimientoOrden;

class TrackingController extends Controller
{
    // Tiempo en segundos por cada paso de la simulación
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

        $orden = Orden::with(['seguimiento', 'vuelo'])->find($orden->id);

        $vuelo    = $orden->vuelo;
        $posicion = null;
        $progreso = 0.0;
        $vueloData = null;

        $ahora = now()->timestamp;
        $inicio = $ahora;

        // 1. LÓGICA SI EL TRANSPORTE ES DRON (Usa la tabla vuelos)
        if ($orden->transporte === 'dron' && $vuelo) {
            if ($vuelo->estado_mision === 'en_vuelo' && $vuelo->hora_despegue) {
                $inicio   = $vuelo->hora_despegue->timestamp;
                $progreso = min(($ahora - $inicio) / self::SEGUNDOS_POR_PASO, 1.0);
                $posicion = [
                    'lat' => (float)$vuelo->lat_origen + ((float)$vuelo->lat_destino - (float)$vuelo->lat_origen) * $progreso,
                    'lng' => (float)$vuelo->lng_origen + ((float)$vuelo->lng_destino - (float)$vuelo->lng_origen) * $progreso,
                ];
            } elseif ($vuelo->estado_mision === 'completado') {
                $progreso = 1.0;
                $posicion = ['lat' => (float)$vuelo->lat_destino, 'lng' => (float)$vuelo->lng_destino];
            }

            $vueloData = [
                'estado_mision' => $vuelo->estado_mision,
                'lat_origen'    => (float)$vuelo->lat_origen,
                'lng_origen'    => (float)$vuelo->lng_origen,
                'lat_destino'   => (float)$vuelo->lat_destino,
                'lng_destino'   => (float)$vuelo->lng_destino,
            ];

        // 2. LÓGICA PARA MOTO O CARRO (Simulación matemática directa sobre la Orden)
        } else {
            // Buscamos el momento en que se marcó como 'recogido' (inicio del viaje)
            $pasoRecogido = $orden->seguimiento->firstWhere('estado', 'recogido');

            // Coordenadas de origen fijas (Tu bodega principal)
            $latO = 7.1254;
            $lngO = -73.1198;

            // Coordenadas de destino dinámicas (guardadas previamente en la orden gracias a la dirección)
            // Si no existen en tu DB, puedes usar estos fallbacks temporales para pruebas
            $latD = (float)($orden->lat_destino ?? 7.1198);
            $lngD = (float)($orden->lng_destino ?? -73.1227);

            if (in_array($orden->estado_entrega, ['en_camino', 'cerca', 'entregado'])) {
                if ($orden->estado_entrega === 'entregado') {
                    $progreso = 1.0;
                } else {
                    $inicio   = $pasoRecogido && $pasoRecogido->completado ? $pasoRecogido->updated_at->timestamp : $ahora;
                    $progreso = min(($ahora - $inicio) / self::SEGUNDOS_POR_PASO, 1.0);
                }

                $posicion = [
                    'lat' => $latO + ($latD - $latO) * $progreso,
                    'lng' => $lngO + ($lngD - $lngO) * $progreso,
                ];
            }

            // Simulamos la estructura 'vuelo' para que el JS del cliente funcione sin cambiar nada
            $vueloData = [
                'estado_mision' => in_array($orden->estado_entrega, ['en_camino', 'cerca']) ? 'en_vuelo' : ($orden->estado_entrega === 'entregado' ? 'completado' : 'pendiente'),
                'lat_origen'    => $latO,
                'lng_origen'    => $lngO,
                'lat_destino'   => $latD,
                'lng_destino'   => $lngD,
            ];
        }

        // Calcular datos para las barras de progreso front-end de los pasos individuales
        $tiempoTranscurrido = $ahora - $inicio;
        $segundosRestantes  = max(self::SEGUNDOS_POR_PASO - $tiempoTranscurrido, 0);
        $progresoPaso       = min(($tiempoTranscurrido / self::SEGUNDOS_POR_PASO) * 100, 100);

        if (!in_array($orden->estado_entrega, ['en_camino', 'cerca'])) {
            $segundosRestantes = 0;
            $progresoPaso = 0;
        }

        $seguimientoData = $orden->seguimiento->map(fn($s) => [
            'estado'       => $s->estado,
            'titulo'       => $s->titulo,
            'descripcion'  => $s->descripcion,
            'icono'        => $s->icono,
            'completado'   => (bool) $s->completado,
            'tiempo'       => $s->completado ? $s->updated_at->format('d/m/Y H:i') : null,
            'foto_entrega' => $s->foto_entrega ? asset($s->foto_entrega) : null,
        ]);

        return response()->json([
            'estado_entrega'      => $orden->estado_entrega,
            'estado_orden'        => $orden->estado,
            'entregado'           => in_array($orden->estado_entrega, ['entregado', 'fallido']),
            'seguimiento'         => $seguimientoData,
            'posicion'            => $posicion,
            'progreso_movimiento' => $progreso,
            'progreso_paso'       => $progresoPaso,
            'segundos_restantes'  => $segundosRestantes,
            'vuelo'               => $vueloData,
            'transporte'          => $orden->transporte,
        ]);
    }

    public static function iniciarSeguimiento(Orden $orden): void
    {
        // Si ya existe seguimiento Y estado_entrega es válido, no tocar
        if (
            $orden->seguimiento()->exists() &&
            !in_array($orden->estado_entrega, ['pendiente_pago', null, ''])
        ) {
            return;
        }

        // Limpiar seguimiento anterior si estaba mal
        $orden->seguimiento()->delete();

        $pasos = self::getPasosSegunTransporte($orden->transporte);

        foreach ($pasos as $i => $paso) {
            \App\Models\SeguimientoOrden::create([
                'orden_id'    => $orden->id,
                'estado'      => $paso['estado'],
                'titulo'      => $paso['titulo'],
                'descripcion' => $paso['descripcion'],
                'icono'       => $paso['icono'],
                'completado'  => $i === 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // estado_entrega apunta al primer paso INCOMPLETO (índice 1 = empacando)
        Orden::where('id', $orden->id)->update([
            'estado_entrega' => $pasos[1]['estado'],
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
                ['estado'=>'en_camino','titulo'=>'Vehículo en camino','descripcion'=>'El vehículo de reparto está en camino.',          'icono'=>'🚗'],
                ['estado'=>'cerca',    'titulo'=>'Vehículo cerca',    'descripcion'=>'El vehículo está llegando a tu zona.',           'icono'=>'📍'],
                ['estado'=>'entregado','titulo'=>'¡Entregado!',       'descripcion'=>'Pedido entregado por vehículo de reparto.',      'icono'=>'✅'],
            ],
            default => [],
        };

        return array_merge($comunes, $especificos);
    }
}
