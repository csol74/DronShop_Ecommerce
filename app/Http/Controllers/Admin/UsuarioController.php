<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount(['ordenes']);

        if ($request->filled('rol')) {
            $query->where('role', $request->rol);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        $usuarios = $query->latest()->paginate(20)->withQueryString();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function cambiarRol(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:cliente,admin,operario,proveedor']);

        abort_if($user->id === auth()->id(), 403, 'No puedes cambiar tu propio rol.');

        $user->update(['role' => $request->role]);
        return back()->with('success', "Rol de {$user->name} actualizado a: " . ucfirst($request->role));
    }
}
