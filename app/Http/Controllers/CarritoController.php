<?php
namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index()
    {
        $items = Carrito::with('producto.categoria')
            ->where('user_id', auth()->id())
            ->get();

        $subtotal   = $items->sum(fn($i) => $i->cantidad * $i->producto->precio);
        $costoEnvio = $this->calcularEnvio(session('transporte', 'moto'), $subtotal);
        $iva        = round($subtotal * 0.19, 2);
        $total      = $subtotal + $costoEnvio + $iva;

        return view('carrito.index', compact('items', 'subtotal', 'costoEnvio', 'iva', 'total'));
    }

    public function agregar(Request $request, Producto $producto)
    {
        if ($producto->stock < 1) {
            return back()->with('error', 'Producto sin stock disponible.');
        }

        $item = Carrito::where('user_id', auth()->id())
            ->where('producto_id', $producto->id)
            ->first();

        if ($item) {
            $nuevaCantidad = $item->cantidad + ($request->cantidad ?? 1);
            if ($nuevaCantidad > $producto->stock) {
                return back()->with('error', 'No hay suficiente stock.');
            }
            $item->update(['cantidad' => $nuevaCantidad]);
        } else {
            Carrito::create([
                'user_id'     => auth()->id(),
                'producto_id' => $producto->id,
                'cantidad'    => $request->cantidad ?? 1,
            ]);
        }

        return back()->with('success', '✓ Producto agregado al carrito.');
    }

    public function actualizar(Request $request, Carrito $carrito)
    {
        abort_if($carrito->user_id !== auth()->id(), 403);

        $request->validate(['cantidad' => 'required|integer|min:1|max:99']);

        if ($request->cantidad > $carrito->producto->stock) {
            return back()->with('error', 'Stock insuficiente.');
        }

        $carrito->update(['cantidad' => $request->cantidad]);
        return back()->with('success', 'Cantidad actualizada.');
    }

    public function eliminar(Carrito $carrito)
    {
        abort_if($carrito->user_id !== auth()->id(), 403);
        $carrito->delete();
        return back()->with('success', 'Producto eliminado del carrito.');
    }

    public function vaciar()
    {
        Carrito::where('user_id', auth()->id())->delete();
        return back()->with('success', 'Carrito vaciado.');
    }

    public function setTransporte(Request $request)
    {
        $request->validate(['transporte' => 'required|in:dron,moto,carro']);
        session(['transporte' => $request->transporte]);
        return back();
    }

    public static function calcularEnvio(string $tipo, float $subtotal): float
    {
        return match($tipo) {
            'dron'  => 15000,  // tarifa fija dron
            'moto'  => 8000,
            'carro' => 12000,
            default => 8000,
        };
    }

    public static function contarItems(): int
    {
        if (!auth()->check()) return 0;
        return Carrito::where('user_id', auth()->id())->sum('cantidad');
    }
}
