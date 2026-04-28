<?php
namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\VueloDron;

class TrackingController extends Controller
{
    // Vista principal de tracking (cliente)
    public function index(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id(), 403);
        $orden->load('seguimiento', 'vuelo.dron', 'items.producto');
        return view('tracking.index', compact('orden'));
    }

    public function estado(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);

        // ============================
        // AUTO-AVANCE POR TIEMPO
        // ============================
        if (!in_array($orden->estado_entrega, ['entregado', 'fallido', 'pendiente_pago'])) {

            $pasoActual = \App\Models\SeguimientoOrden::where('orden_id', $orden->id)
                ->where('estado', $orden->estado_entrega)
                ->first();

            if ($pasoActual) {
                $referencia       = max(
                    $pasoActual->created_at->timestamp,
                    $pasoActual->updated_at->timestamp
                );

                $segundosEnEstado = now()->timestamp - $referencia;

                // ⏱️ Si han pasado 15 segundos → avanzar automáticamente
                if ($segundosEnEstado >= 40) {
                    \App\Http\Controllers\Admin\DronController::procesarAvance($orden);

                    // Recargar la orden después del avance
                    $orden = Orden::find($orden->id);
                }
            }
        }

        // ============================
        // 🔄 SIEMPRE RECARGAR FRESCO
        // ============================
        $orden = Orden::with(['seguimiento', 'vuelo'])->find($orden->id);

        $vuelo    = $orden->vuelo;
        $posicion = null;

        if ($vuelo) {
            if ($vuelo->estado_mision === 'en_vuelo') {
                $posicion = $this->interpolarPosicion($vuelo);
            } elseif ($vuelo->estado_mision === 'completado') {
                $posicion = [
                    'lat' => (float) $vuelo->lat_destino,
                    'lng' => (float) $vuelo->lng_destino,
                ];
            }
        }

        $seguimientoData = $orden->seguimiento->map(fn($s) => [
            'estado'      => $s->estado,
            'titulo'      => $s->titulo,
            'descripcion' => $s->descripcion,
            'icono'       => $s->icono,
            'completado'  => (bool) $s->completado,
            'tiempo'      => $s->completado
                ? $s->updated_at->format('d/m/Y H:i')
                : null,
        ]);

        return response()->json([
            'estado_entrega' => $orden->estado_entrega,
            'estado_orden'   => $orden->estado,
            'entregado'      => in_array($orden->estado_entrega, ['entregado', 'fallido']),
            'seguimiento'    => $seguimientoData,
            'posicion'       => $posicion,
            'vuelo'          => $vuelo ? [
                'estado_mision' => $vuelo->estado_mision,
                'lat_origen'    => (float) $vuelo->lat_origen,
                'lng_origen'    => (float) $vuelo->lng_origen,
                'lat_destino'   => (float) $vuelo->lat_destino,
                'lng_destino'   => (float) $vuelo->lng_destino,
            ] : null,
            'transporte'     => $orden->transporte,
        ]);
    }

    private function interpolarPosicion(VueloDron $vuelo): array
    {
        if (!$vuelo->hora_despegue) {
            return [
                'lat' => (float) $vuelo->lat_origen,
                'lng' => (float) $vuelo->lng_origen,
            ];
        }

        $inicio   = $vuelo->hora_despegue->timestamp;
        $ahora    = now()->timestamp;
        $duracion = 60; // segundos para completar el trayecto
        $progreso = min(($ahora - $inicio) / $duracion, 1.0);

        return [
            'lat' => (float) $vuelo->lat_origen + ((float) $vuelo->lat_destino - (float) $vuelo->lat_origen) * $progreso,
            'lng' => (float) $vuelo->lng_origen + ((float) $vuelo->lng_destino - (float) $vuelo->lng_origen) * $progreso,
        ];
    }

    public static function iniciarSeguimiento(Orden $orden): void
    {
        if ($orden->seguimiento()->exists()) return;

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
            ['estado'=>'pago_confirmado', 'titulo'=>'Pago confirmado',    'descripcion'=>'Tu pago fue procesado exitosamente.',          'icono'=>'✅'],
            ['estado'=>'empacando',       'titulo'=>'Preparando pedido',   'descripcion'=>'El equipo está empacando tus productos.',       'icono'=>'📦'],
            ['estado'=>'recogido',        'titulo'=>'Pedido recogido',     'descripcion'=>'El transportador recogió tu paquete.',          'icono'=>'🏭'],
        ];

        $especificos = match($transporte) {
            'dron' => [
                ['estado'=>'en_camino', 'titulo'=>'Dron en vuelo',      'descripcion'=>'Tu paquete viaja en nuestro dron DronShop Alpha-1.','icono'=>'🚁'],
                ['estado'=>'cerca',     'titulo'=>'Dron cerca',         'descripcion'=>'El dron está a menos de 500m de tu ubicación.',     'icono'=>'📡'],
                ['estado'=>'entregado', 'titulo'=>'¡Entregado!',        'descripcion'=>'Tu pedido fue entregado por el dron.',              'icono'=>'🎯'],
            ],
            'moto' => [
                ['estado'=>'en_camino', 'titulo'=>'Moto en camino',     'descripcion'=>'El mensajero está en camino con tu pedido.',        'icono'=>'🏍️'],
                ['estado'=>'cerca',     'titulo'=>'Mensajero cerca',    'descripcion'=>'El mensajero está llegando a tu dirección.',        'icono'=>'📍'],
                ['estado'=>'entregado', 'titulo'=>'¡Entregado!',        'descripcion'=>'Pedido entregado por mensajero en moto.',           'icono'=>'✅'],
            ],
            'carro' => [
                ['estado'=>'en_camino', 'titulo'=>'Vehículo en camino', 'descripcion'=>'El vehículo de reparto está en camino.',            'icono'=>'🚗'],
                ['estado'=>'cerca',     'titulo'=>'Vehículo cerca',     'descripcion'=>'El vehículo está llegando a tu zona.',              'icono'=>'📍'],
                ['estado'=>'entregado', 'titulo'=>'¡Entregado!',        'descripcion'=>'Pedido entregado por vehículo de reparto.',         'icono'=>'✅'],
            ],
            default => [],
        };

        return array_merge($comunes, $especificos);
    }
}
