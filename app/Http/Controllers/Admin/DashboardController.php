<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\User;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // KPIs
        $totalVentas     = Orden::whereIn('estado', ['pagado', 'en_despacho', 'entregado'])->sum('total');
        $totalOrdenes    = Orden::count();
        $ordenesPendientes = Orden::where('estado', 'pendiente')->count();
        $totalUsuarios   = User::where('role', 'cliente')->count();
        $totalProductos  = Producto::count();
        $stockBajo       = Producto::whereColumn('stock', '<=', 'stock_minimo')->where('activo', true)->count();

        // Ventas por categoría
        $ventasPorCategoria = DB::table('orden_items')
            ->join('productos', 'orden_items.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->join('ordenes', 'orden_items.orden_id', '=', 'ordenes.id')
            ->whereIn('ordenes.estado', ['pagado', 'en_despacho', 'entregado'])
            ->select('categorias.nombre', DB::raw('SUM(orden_items.subtotal) as total'))
            ->groupBy('categorias.nombre')
            ->get();

        // Últimas 8 órdenes
        $ultimasOrdenes = Orden::with('user')
            ->latest()
            ->limit(8)
            ->get();

        // Productos con stock bajo
        $productosBajoStock = Producto::with('categoria')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->where('activo', true)
            ->limit(5)
            ->get();

        // Ventas últimos 7 días
        $ventasSemana = Orden::whereIn('estado', ['pagado', 'en_despacho', 'entregado'])
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return view('admin.dashboard', compact(
            'totalVentas', 'totalOrdenes', 'ordenesPendientes',
            'totalUsuarios', 'totalProductos', 'stockBajo',
            'ventasPorCategoria', 'ultimasOrdenes',
            'productosBajoStock', 'ventasSemana'
        ));
    }
}
