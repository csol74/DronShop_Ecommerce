<?php
namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Orden;
use App\Models\OrdenItem;
use App\Http\Controllers\CarritoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenController extends Controller
{
    public function checkout()
    {
        $items = Carrito::with('producto')
            ->where('user_id', auth()->id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        $transporte = session('transporte', 'moto');
        $subtotal   = $items->sum(fn($i) => $i->cantidad * $i->producto->precio);
        $costoEnvio = CarritoController::calcularEnvio($transporte, $subtotal);
        $iva        = round($subtotal * 0.19, 2);
        $total      = $subtotal + $costoEnvio + $iva;

        return view('orden.checkout', compact('items', 'transporte', 'subtotal', 'costoEnvio', 'iva', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'direccion_entrega' => 'required|string|max:255',
            'ciudad'            => 'required|string|max:100',
            'transporte'        => 'required|in:dron,moto,carro',
            'notas'             => 'nullable|string|max:500',
        ]);

        $items = Carrito::with('producto')
            ->where('user_id', auth()->id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        // Verificar stock antes de crear
        foreach ($items as $item) {
            if ($item->cantidad > $item->producto->stock) {
                return back()->with('error', "Stock insuficiente para: {$item->producto->nombre}");
            }
        }

        $subtotal   = $items->sum(fn($i) => $i->cantidad * $i->producto->precio);
        $costoEnvio = CarritoController::calcularEnvio($request->transporte, $subtotal);
        $iva        = round($subtotal * 0.19, 2);
        $total      = $subtotal + $costoEnvio + $iva;

        DB::transaction(function () use ($request, $items, $subtotal, $costoEnvio, $iva, $total, &$orden) {
            $orden = Orden::create([
                'codigo'            => Orden::generarCodigo(),
                'user_id'           => auth()->id(),
                'estado'            => 'pendiente',
                'transporte'        => $request->transporte,
                'subtotal'          => $subtotal,
                'costo_envio'       => $costoEnvio,
                'iva'               => $iva,
                'total'             => $total,
                'direccion_entrega' => $request->direccion_entrega,
                'ciudad'            => $request->ciudad,
                'notas'             => $request->notas,
            ]);

            foreach ($items as $item) {
                OrdenItem::create([
                    'orden_id'        => $orden->id,
                    'producto_id'     => $item->producto_id,
                    'nombre_producto' => $item->producto->nombre,
                    'precio_unitario' => $item->producto->precio,
                    'cantidad'        => $item->cantidad,
                    'subtotal'        => $item->cantidad * $item->producto->precio,
                ]);

                // Descontar stock
                $item->producto->decrement('stock', $item->cantidad);
            }

            // Vaciar carrito
            Carrito::where('user_id', auth()->id())->delete();
            session()->forget('transporte');
        });

        return redirect()->route('orden.pago', $orden)->with('success', 'Orden creada. Procede al pago.');
    }

    public function pago(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id(), 403);
        return view('orden.pago', compact('orden'));
    }

    public function historial()
    {
        $ordenes = Orden::with(['items', 'seguimiento'])
        ->where('user_id', auth()->id())
        ->latest()
        ->paginate(10);

        return view('orden.historial', compact('ordenes'));
    }

    public function show(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $orden->load('items.producto', 'user');
        return view('orden.show', compact('orden'));
    }

    public function cancelar(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id(), 403);

        if ($orden->estado !== 'pendiente') {
            return back()->with('error', 'Solo puedes cancelar órdenes en estado Pendiente.');
        }

        DB::transaction(function () use ($orden) {
            // Restaurar stock
            foreach ($orden->items as $item) {
                $item->producto->increment('stock', $item->cantidad);
            }
            $orden->update(['estado' => 'cancelado']);
        });

        return redirect()->route('orden.historial')->with('success', 'Orden cancelada y stock restaurado.');
    }
}
