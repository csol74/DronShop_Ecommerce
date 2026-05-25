<?php
namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    // Verificar que el producto pertenece al proveedor
    private function verificarPropiedad(Producto $producto): void
    {
        $proveedor = auth()->user()->proveedor;
        abort_if(!$proveedor || $producto->proveedor_id !== $proveedor->id, 403,
            'Este producto no pertenece a tu cuenta.');
    }

    public function index(Request $request)
    {
        $proveedor = auth()->user()->proveedor;

        if (!$proveedor) {
            return view('proveedor.sin-asignar');
        }

        $query = Producto::with('categoria')
            ->where('proveedor_id', $proveedor->id);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $productos  = $query->latest()->paginate(12)->withQueryString();
        $categorias = Categoria::all();

        return view('proveedor.productos.index', compact('productos', 'categorias', 'proveedor'));
    }

    public function create()
    {
        $proveedor = auth()->user()->proveedor;
        abort_if(!$proveedor, 403, 'No tienes un perfil de proveedor asignado.');
        $categorias = Categoria::all();
        return view('proveedor.productos.form', compact('categorias', 'proveedor'));
    }

    public function store(Request $request)
    {
        $proveedor = auth()->user()->proveedor;
        abort_if(!$proveedor, 403);

        $data = $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'required|string',
            'precio'          => 'required|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'stock_minimo'    => 'required|integer|min:1',
            'peso_kg'         => 'required|numeric|min:0.01',
            'imagen'          => 'required|url',
            'categoria_id'    => 'required|exists:categorias,id',
            'activo'          => 'boolean',
            'caracteristicas' => 'nullable|string',
        ]);

        $data['slug']         = Str::slug($data['nombre']) . '-' . Str::random(5);
        $data['proveedor_id'] = $proveedor->id;
        $data['activo']       = $request->boolean('activo', true);
        $data['caracteristicas'] = $this->parsearCaracteristicas($request->caracteristicas);

        Producto::create($data);

        return redirect()->route('proveedor.productos.index')
            ->with('success', 'Producto publicado en el catálogo.');
    }

    public function edit(Producto $producto)
    {
        $this->verificarPropiedad($producto);
        $categorias = Categoria::all();
        $caracText  = $producto->caracteristicas
            ? collect($producto->caracteristicas)->map(fn($v, $k) => "$k: $v")->implode("\n")
            : '';
        return view('proveedor.productos.form', compact('producto', 'categorias', 'caracText'));
    }

    public function update(Request $request, Producto $producto)
    {
        $this->verificarPropiedad($producto);

        $data = $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'required|string',
            'precio'          => 'required|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'stock_minimo'    => 'required|integer|min:1',
            'peso_kg'         => 'required|numeric|min:0.01',
            'imagen'          => 'required|url',
            'categoria_id'    => 'required|exists:categorias,id',
            'activo'          => 'boolean',
            'caracteristicas' => 'nullable|string',
        ]);

        $data['activo']          = $request->boolean('activo', true);
        $data['caracteristicas'] = $this->parsearCaracteristicas($request->caracteristicas);

        $producto->update($data);

        return redirect()->route('proveedor.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function toggleActivo(Producto $producto)
    {
        $this->verificarPropiedad($producto);
        $producto->update(['activo' => !$producto->activo]);
        return back()->with('success', 'Estado del producto actualizado.');
    }

    private function parsearCaracteristicas(?string $texto): ?array
    {
        if (!$texto) return null;
        $result = [];
        foreach (explode("\n", trim($texto)) as $linea) {
            $linea = trim($linea);
            if ($linea && str_contains($linea, ':')) {
                [$key, $val] = explode(':', $linea, 2);
                $result[trim($key)] = trim($val);
            }
        }
        return $result ?: null;
    }
}
