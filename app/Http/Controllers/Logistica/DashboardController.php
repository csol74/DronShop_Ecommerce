<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Órdenes asignadas a este logístico:
        // Trae las activas O las que ya se entregaron hoy según su ruta de seguimiento
        $misOrdenes = Orden::with(['user', 'seguimiento', 'items'])
            ->where('logistica_user_id', $userId)
            ->where(function($query) {
                $query->whereNotIn('estado_entrega', ['entregado', 'fallido'])
                      ->orWhere(function($q) {
                          $q->where('estado_entrega', 'entregado')
                            ->whereHas('seguimiento', function($sub) {
                                $sub->where('estado', 'entregado')
                                    ->whereDate('updated_at', Carbon::today());
                            });
                      });
            })
            ->latest()
            ->get();

        // Órdenes disponibles para tomar
        $disponibles = Orden::with(['user', 'items'])
            ->whereIn('estado', ['pagado', 'en_despacho'])
            ->whereNull('logistica_user_id')
            ->whereNotIn('estado_entrega', ['entregado', 'fallido', 'pendiente_pago'])
            ->latest()
            ->get();

        // Estadísticas de KPIs corregidas mediante relación de seguimiento
        $entregadas = Orden::where('logistica_user_id', $userId)
            ->where('estado_entrega', 'entregado')
            ->whereHas('seguimiento', function($q) {
                $q->where('estado', 'entregado')
                  ->whereDate('updated_at', Carbon::today());
            })
            ->count();

        $enProceso = Orden::where('logistica_user_id', $userId)
            ->whereNotIn('estado_entrega', ['entregado', 'fallido'])
            ->count();

        return view('logistica.dashboard', compact(
            'misOrdenes', 'disponibles', 'entregadas', 'enProceso'
        ));
    }
}
