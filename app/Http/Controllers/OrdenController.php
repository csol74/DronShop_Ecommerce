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
        // 1. Obtener los productos del carrito del usuario autenticado
        $items = Carrito::with('producto')
            ->where('user_id', auth()->id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        // 2. Verificar suscripción SkyPass y aplicar beneficios
        $usuario   = auth()->user();
        $tieneSP   = $usuario->tieneSkyPass();
        $descuento = 0;

        $subtotal  = $items->sum(fn($i) => $i->cantidad * $i->producto->precio);

        if ($tieneSP) {
            $descuento = round($subtotal * 0.10, 2); // 10% de descuento en productos
        }

        // Si tiene SkyPass el envío es 0, de lo contrario se calcula según el transporte (por defecto moto)
        $transporte = session('transporte', 'moto');
        $costoEnvio = $tieneSP ? 0 : CarritoController::calcularEnvio($transporte, $subtotal);

        // El IVA se calcula sobre el subtotal ya neto (restando el descuento)
        $iva        = round(($subtotal - $descuento) * 0.19, 2);
        $total      = $subtotal - $descuento + $costoEnvio + $iva;

        return view('orden.checkout', compact('items', 'transporte', 'subtotal', 'descuento', 'costoEnvio', 'iva', 'total'));
    }

    public function store(Request $request)
    {
        // 1. Validar el formulario de entrega y transporte (¡Ya estaba perfecto!)
        $request->validate([
            'direccion_entrega' => 'required|string|max:255',
            'ciudad'            => 'required|string|max:100',
            'transporte'        => 'required|in:dron,moto,carro',
            'notas'             => 'nullable|string|max:500',
            'lat_destino'       => 'nullable|numeric',
            'lng_destino'       => 'nullable|numeric',
        ]);

        // 2. Obtener los productos del carrito
        $items = Carrito::with('producto')
            ->where('user_id', auth()->id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        // 3. Verificar que haya stock suficiente para procesar la orden
        foreach ($items as $item) {
            if ($item->cantidad > $item->producto->stock) {
                return back()->with('error', "Stock insuficiente para: {$item->producto->nombre}");
            }
        }

        // 4. Recalcular costos en el servidor aplicando beneficios de SkyPass
        $usuario   = auth()->user();
        $tieneSP   = $usuario->tieneSkyPass();
        $descuento = 0;

        $subtotal  = $items->sum(fn($i) => $i->cantidad * $i->producto->precio);

        if ($tieneSP) {
            $descuento = round($subtotal * 0.10, 2); // 10% descuento
        }

        // Envío gratis si es SkyPass, si no, se calcula con el transporte seleccionado en el Request
        $costoEnvio = $tieneSP ? 0 : CarritoController::calcularEnvio($request->transporte, $subtotal);
        $iva        = round(($subtotal - $descuento) * 0.19, 2);
        $total      = $subtotal - $descuento + $costoEnvio + $iva;

        // 5. Transacción de Base de Datos para asegurar la integridad de la compra
        DB::transaction(function () use ($request, $items, $subtotal, $descuento, $costoEnvio, $iva, $total, &$orden) {
            $orden = Orden::create([
                'codigo'            => Orden::generarCodigo(),
                'user_id'           => auth()->id(),
                'estado'            => 'pendiente',
                'transporte'        => $request->transporte,
                'subtotal'          => $subtotal,
                'descuento'         => $descuento,
                'costo_envio'       => $costoEnvio,
                'iva'               => $iva,
                'total'             => $total,
                'direccion_entrega' => $request->direccion_entrega,
                'ciudad'            => $request->ciudad,
                'notas'             => $request->notas,

                // 🗺️ LOGÍSTICA: Guardamos las coordenadas obtenidas en el frontend
                'lat_destino'       => $request->lat_destino,
                'lng_destino'       => $request->lng_destino,
            ]);

            // Registrar cada producto de forma independiente en la tabla de ítems de la orden
            foreach ($items as $item) {
                OrdenItem::create([
                    'orden_id'        => $orden->id,
                    'producto_id'     => $item->producto_id,
                    'nombre_producto' => $item->producto->nombre,
                    'precio_unitario' => $item->producto->precio,
                    'cantidad'        => $item->cantidad,
                    'subtotal'        => $item->cantidad * $item->producto->precio,
                ]);

                // Descontar las unidades del stock del producto
                $item->producto->decrement('stock', $item->cantidad);
            }

            // Limpiar los datos temporales del carrito y la sesión de transporte
            Carrito::where('user_id', auth()->id())->delete();
            session()->forget('transporte');
        });

        return redirect()->route('orden.pago', $orden)->with('success', 'Orden creada. Procede al pago.');
    }

    public function pago(Orden $orden)
    {
        // Evitar que un usuario vea pasarelas de pago de terceros
        abort_if($orden->user_id !== auth()->id(), 403);
        return view('orden.pago', compact('orden'));
    }

    public function historial()
    {
        // Obtener órdenes del usuario con paginación de 10 registros
        $ordenes = Orden::with(['items', 'seguimiento'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('orden.historial', compact('ordenes'));
    }

    public function show(Orden $orden)
    {
        // Permitir visualización solo al dueño de la orden o al Administrador del sistema
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

        // Devolver las cantidades retenidas al stock general si se cancela antes de pagar
        DB::transaction(function () use ($orden) {
            foreach ($orden->items as $item) {
                $item->producto->increment('stock', $item->cantidad);
            }
            $orden->update(['estado' => 'cancelado']);
        });

        return redirect()->route('orden.historial')->with('success', 'Orden cancelada y stock restaurado.');
    }
}
