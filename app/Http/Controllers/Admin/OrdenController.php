<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class OrdenController extends Controller
{
    public function index(Request $request)
    {
        $query = Orden::with(['user', 'items']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $query->where('codigo', 'like', '%' . $request->buscar . '%')
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->buscar . '%'));
        }
        if ($request->filled('transporte')) {
            $query->where('transporte', $request->transporte);
        }

        $ordenes = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pendiente'   => Orden::where('estado', 'pendiente')->count(),
            'pagado'      => Orden::where('estado', 'pagado')->count(),
            'en_despacho' => Orden::where('estado', 'en_despacho')->count(),
            'entregado'   => Orden::where('estado', 'entregado')->count(),
            'cancelado'   => Orden::where('estado', 'cancelado')->count(),
        ];

        return view('admin.ordenes.index', compact('ordenes', 'stats'));
    }

    public function show(Orden $orden)
    {
        $orden->load('items.producto', 'user');
        return view('admin.ordenes.show', compact('orden'));
    }

    public function asignarLogistica(Request $request, Orden $orden)
    {
        $request->validate([
            'logistica_user_id' => 'nullable|exists:users,id'
        ]);

        $orden->update([
            'logistica_user_id' => $request->logistica_user_id ?: null
        ]);

        $orden->refresh();

        // Iniciar o corregir seguimiento si está en estado inválido
        if (in_array($orden->estado, ['pagado', 'en_despacho'])) {
            if (
                !$orden->seguimiento()->exists() ||
                in_array($orden->estado_entrega, ['pendiente_pago', null, ''])
            ) {
                \App\Http\Controllers\TrackingController::iniciarSeguimiento($orden);
            }
        }

        return back()->with('success', '✓ Logístico asignado. La orden ya aparece en su panel.');
    }
}
