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

    public function actualizarEstado(Request $request, Orden $orden)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,pagado,en_despacho,entregado,cancelado',
        ]);

        $orden->update(['estado' => $request->estado]);

        return back()->with('success', "Orden {$orden->codigo} actualizada a: " . ucfirst($request->estado));
    }
}
