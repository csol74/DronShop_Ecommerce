<?php
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'proveedor'])->where('activo', true);

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', fn($q) => $q->where('slug', $request->categoria));
        }

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }

        if ($request->filled('disponible')) {
            $query->where('stock', '>', 0);
        }

        $productos   = $query->latest()->paginate(12)->withQueryString();
        $categorias  = Categoria::all();

        return view('catalogo.index', compact('productos', 'categorias'));
    }

    public function show(string $slug)
    {
        $producto  = Producto::with(['categoria', 'proveedor'])
            ->where('slug', $slug)
            ->where('activo', true)
            ->firstOrFail();

        $relacionados = Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->where('activo', true)
            ->limit(4)
            ->get();

        return view('catalogo.show', compact('producto', 'relacionados'));
    }
}
