<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::withCount('productos')->latest()->paginate(15);
        return view('admin.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('admin.proveedores.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'empresa'     => 'required|string|max:255',
            'email'       => 'required|email|unique:proveedores',
            'telefono'    => 'required|string|max:20',
            'pais'        => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'logo'        => 'nullable|url',
        ]);

        Proveedor::create($data);
        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor registrado.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('admin.proveedores.form', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'empresa'     => 'required|string|max:255',
            'email'       => 'required|email|unique:proveedores,email,' . $proveedor->id,
            'telefono'    => 'required|string|max:20',
            'pais'        => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'logo'        => 'nullable|url',
        ]);

        $proveedor->update($data);
        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return back()->with('success', 'Proveedor eliminado.');
    }
}
