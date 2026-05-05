<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\RolActualizadoNotification;

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
        $request->validate([
            'role' => 'required|in:cliente,admin,proveedor'
        ]);

        abort_if($user->id === auth()->id(), 403);

        $user->update(['role' => $request->role]);

        // 🔥 SI ES PROVEEDOR → CREAR PROVEEDOR AUTOMÁTICO
        if ($request->role === 'proveedor') {

            if (!$user->proveedor) {
                \App\Models\Proveedor::create([
                    'nombre'   => $user->name,
                    'empresa'  => 'Empresa de ' . $user->name,
                    'email'    => $user->email,
                    'telefono' => 'N/A',
                    'pais'     => 'Colombia',
                    'user_id'  => $user->id,
                ]);
            }
        }

        // 🔔 Notificación
        $user->notify(new RolActualizadoNotification($request->role));

        return back()->with('success', "Rol actualizado correctamente.");
    }
}
