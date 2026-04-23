<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'proveedor']);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }
        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $productos  = $query->latest()->paginate(15)->withQueryString();
        $categorias = Categoria::all();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias  = Categoria::all();
        $proveedores = Proveedor::all();
        return view('admin.productos.form', compact('categorias', 'proveedores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'required|string',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
            'peso_kg'      => 'required|numeric|min:0',
            'imagen'       => 'required|url',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'activo'       => 'boolean',
            'caracteristicas' => 'nullable|string',
        ]);

        $data['slug']            = Str::slug($data['nombre']) . '-' . Str::random(4);
        $data['activo']          = $request->boolean('activo', true);
        $data['caracteristicas'] = $this->parsearCaracteristicas($request->caracteristicas);

        Producto::create($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias  = Categoria::all();
        $proveedores = Proveedor::all();
        $caracText   = $producto->caracteristicas
            ? collect($producto->caracteristicas)->map(fn($v, $k) => "$k: $v")->implode("\n")
            : '';
        return view('admin.productos.form', compact('producto', 'categorias', 'proveedores', 'caracText'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'required|string',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
            'peso_kg'      => 'required|numeric|min:0',
            'imagen'       => 'required|url',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'activo'       => 'boolean',
            'caracteristicas' => 'nullable|string',
        ]);

        $data['activo']          = $request->boolean('activo', true);
        $data['caracteristicas'] = $this->parsearCaracteristicas($request->caracteristicas);

        $producto->update($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);
        return back()->with('success', 'Producto desactivado.');
    }

    public function toggleActivo(Producto $producto)
    {
        $producto->update(['activo' => !$producto->activo]);
        return back()->with('success', 'Estado del producto actualizado.');
    }

    private function parsearCaracteristicas(?string $texto): ?array
    {
        if (!$texto) return null;
        $result = [];
        foreach (explode("\n", trim($texto)) as $linea) {
            if (str_contains($linea, ':')) {
                [$key, $val] = explode(':', $linea, 2);
                $result[trim($key)] = trim($val);
            }
        }
        return $result ?: null;
    }
}
