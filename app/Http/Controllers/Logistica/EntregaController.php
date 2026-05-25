<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TrackingController;
use App\Models\Orden;
use App\Models\SeguimientoOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EntregaController extends Controller
{
    // Ver detalle de una orden asignada
    public function show(Orden $orden)
    {
        $this->verificarAcceso($orden);
        $orden->load('seguimiento', 'items.producto', 'user');
        return view('logistica.entrega', compact('orden'));
    }

    // Tomar una orden disponible
    public function tomar(Orden $orden)
    {
        abort_if(
            $orden->logistica_user_id && $orden->logistica_user_id !== auth()->id(),
            403, 'Esta orden ya fue tomada por otro logístico.'
        );

        $orden->update(['logistica_user_id' => auth()->id()]);

        return redirect()->route('logistica.entrega.show', $orden)
            ->with('success', '✓ Orden asignada. ¡Manos a la obra!');
    }

    // Avanzar un paso del seguimiento (CORREGIDO Y BULLETPROOF)
    public function avanzarPaso(Request $request, Orden $orden)
    {
        $this->verificarAcceso($orden);

        if (in_array($orden->estado_entrega, ['entregado', 'fallido'])) {
            return back()->with('error', 'Esta orden ya finalizó.');
        }

        // 1. Obtener el flujo de pasos teóricos según el transporte
        $pasos   = TrackingController::getPasosSegunTransporte($orden->transporte);
        $estados = array_column($pasos, 'estado');

        // 2. Buscar el VERDADERO paso actual en la BD (el primero que NO esté completado)
        $pasoActualBD = $orden->seguimiento()
            ->where('completado', false)
            ->orderBy('id')
            ->first();

        if (!$pasoActualBD) {
            return back()->with('error', 'No hay pasos pendientes en el seguimiento.');
        }

        // 3. Encontrar la posición de este estado en el array de configuración
        $idx = array_search($pasoActualBD->estado, $estados);

        if ($idx === false) {
            return back()->with('error', 'El paso actual no coincide con la configuración del transporte.');
        }

        // 4. Validaciones de topes para evitar saltarse la foto obligatoria
        if ($idx >= count($estados) - 1 || $pasoActualBD->estado === 'mensajero_cerca' || $pasoActualBD->estado === 'cerca') {
            return back()->with('error', 'Usa el formulario de foto para el último paso.');
        }

        $siguiente = $pasos[$idx + 1];

        if ($siguiente['estado'] === 'entregado') {
            return back()->with('error', 'Para el último paso sube la foto de entrega.');
        }

        // 5. Procesar el cambio de estado de forma segura
        DB::transaction(function () use ($orden, $pasoActualBD, $siguiente) {

            // Marcar el paso actual específico como completado usando su ID único
            $pasoActualBD->update([
                'completado'        => true,
                'logistica_user_id' => auth()->id(),
                'updated_at'        => now(),
            ]);

            // Asegurar que el siguiente paso inmediato esté activo e incompleto
            $orden->seguimiento()
                ->where('estado', $siguiente['estado'])
                ->update([
                    'completado'        => false,
                    'logistica_user_id' => null,
                    'updated_at'        => now(),
                ]);

            // Sincronizar el estado maestro de la orden
            $orden->update([
                'estado_entrega' => $siguiente['estado'],
                'estado' => $siguiente['estado'] === 'en_camino'
                    ? 'en_despacho'
                    : $orden->estado,
            ]);
        });

        // Control de Drones
        if ($orden->transporte === 'dron' && $siguiente['estado'] === 'en_camino') {
            app(\App\Http\Controllers\Admin\DronController::class)
                ->gestionarVueloDron(
                    $orden->fresh(['items.producto']),
                    'en_camino'
                );
        }

        $orden->refresh();
        $orden->load('seguimiento');

        return redirect()->route('logistica.entrega.show', $orden->id)
            ->with('success', '✓ ' . $siguiente['titulo'] . ' — marcado como completado.');
    }

    // Confirmar entrega CON foto obligatoria (CORREGIDO)
    public function confirmarEntrega(Request $request, Orden $orden)
    {
        $this->verificarAcceso($orden);

        $request->validate([
            'foto_entrega' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'lat'          => 'nullable|numeric',
            'lng'          => 'nullable|numeric',
        ], [
            'foto_entrega.required' => 'Debes subir una foto del paquete entregado.',
            'foto_entrega.image'    => 'El archivo debe ser una imagen.',
            'foto_entrega.max'      => 'La imagen no puede superar 5MB.',
        ]);

        // SOLUCIÓN: Validamos directamente sobre el registro final 'entregado'
        $pasoFinalBD = $orden->seguimiento()
            ->where('estado', 'entregado')
            ->first();

        if (!$pasoFinalBD) {
            return back()->with('error', 'El paso de entrega final no está configurado para esta orden.');
        }

        if ($pasoFinalBD->completado || $orden->estado_entrega === 'entregado') {
            return back()->with('error', 'Esta entrega ya fue confirmada previamente.');
        }

        // Guardar foto
        $ruta        = $request->file('foto_entrega')->store('entregas', 'public');
        $rutaPublica = 'storage/' . $ruta;

        DB::transaction(function () use ($orden, $rutaPublica, $request) {
            $pasos   = TrackingController::getPasosSegunTransporte($orden->transporte);
            $estados = array_column($pasos, 'estado');

            // Marcar TODOS los pasos de la ruta de seguimiento como completados
            foreach ($estados as $estado) {
                $update = [
                    'completado'        => true,
                    'logistica_user_id' => auth()->id(),
                    'updated_at'        => now(),
                ];

                // Solo al paso entregado le inyectamos la foto y el GPS
                if ($estado === 'entregado') {
                    $update['foto_entrega'] = $rutaPublica;
                    if ($request->lat) $update['lat'] = $request->lat;
                    if ($request->lng) $update['lng'] = $request->lng;
                }

                $orden->seguimiento()
                    ->where('estado', $estado)
                    ->update($update);
            }

            // Cerrar orden permanentemente en el Estado Maestro
            $orden->update([
                'estado_entrega' => 'entregado',
                'estado'         => 'entregado',
            ]);

            // Cerrar vuelo si es dron
             // Cerrar vuelo si es dron
            if ($orden->vuelo) {
                $orden->vuelo->update([
                    'estado_mision'   => 'completado',
                    'hora_aterrizaje' => now(),
                ]);

                $dron = \App\Models\Dron::first();
                if ($dron) {
                    // Consumir batería al completar entrega: 5-10%
                    $nuevaBateria = max(0, $dron->bateria_actual_pct - rand(5, 10));
                    $dron->update([
                        'estado'             => 'disponible',
                        'bateria_actual_pct' => $nuevaBateria,
                    ]);
                }
            }
        });

        // SOLUCIÓN CRÍTICA: Forzar a Laravel a sincronizar el objeto con la BD real
        $orden->refresh();

        return redirect()->route('logistica.dashboard')
            ->with('success', '🎉 Entrega confirmada exitosamente.');
    }

    private function verificarAcceso(Orden $orden): void
    {
        abort_if(
            $orden->logistica_user_id &&
            $orden->logistica_user_id !== auth()->id() &&
            !auth()->user()->isAdmin(),
            403
        );
    }
}
