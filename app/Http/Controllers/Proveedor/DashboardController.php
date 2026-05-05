<?php
namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
    {
        public function index()
        {
            $proveedor = auth()->user()->proveedor;

            if (!$proveedor) {
                return view('proveedor.sin-asignar');
            }

            $totalProductos  = $proveedor->productos()->count();
            $productosActivos = $proveedor->productos()->where('activo', true)->count();
            $stockBajo       = $proveedor->productos()
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->where('activo', true)
                ->count();

            // Ventas de sus productos
            $totalVentas = DB::table('orden_items')
                ->join('productos', 'orden_items.producto_id', '=', 'productos.id')
                ->join('ordenes', 'orden_items.orden_id', '=', 'ordenes.id')
                ->where('productos.proveedor_id', $proveedor->id)
                ->whereIn('ordenes.estado', ['pagado', 'en_despacho', 'entregado'])
                ->sum('orden_items.subtotal');

            $topProductos = DB::table('orden_items')
                ->join('productos', 'orden_items.producto_id', '=', 'productos.id')
                ->join('ordenes', 'orden_items.orden_id', '=', 'ordenes.id')
                ->where('productos.proveedor_id', $proveedor->id)
                ->whereIn('ordenes.estado', ['pagado', 'en_despacho', 'entregado'])
                ->select(
                    'productos.nombre',
                    'productos.imagen',
                    DB::raw('SUM(orden_items.cantidad) as unidades'),
                    DB::raw('SUM(orden_items.subtotal) as total')
                )
                ->groupBy('productos.id', 'productos.nombre', 'productos.imagen')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $productosStockBajo = $proveedor->productos()
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->where('activo', true)
                ->get();

            return view('proveedor.dashboard', compact(
                'proveedor', 'totalProductos', 'productosActivos',
                'stockBajo', 'totalVentas', 'topProductos', 'productosStockBajo'
            ));
        }
    }
