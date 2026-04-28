<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dron;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function index()
    {
        $dron           = Dron::first();
        $mantenimientos = Mantenimiento::where('dron_id', $dron->id)
            ->latest('fecha_programada')->paginate(15);
        $proximos       = Mantenimiento::where('dron_id', $dron->id)
            ->where('estado', 'pendiente')
            ->where('fecha_programada', '>=', now())
            ->orderBy('fecha_programada')
            ->get();
        return view('admin.dron.mantenimiento', compact('dron', 'mantenimientos', 'proximos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'             => 'required|in:preventivo,correctivo',
            'descripcion'      => 'required|string',
            'fecha_programada' => 'required|date',
            'tecnico'          => 'nullable|string',
            'costo'            => 'nullable|numeric',
            'observaciones'    => 'nullable|string',
        ]);
        $data['dron_id'] = Dron::first()->id;
        Mantenimiento::create($data);
        return back()->with('success', 'Mantenimiento programado correctamente.');
    }

    public function actualizar(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate(['estado' => 'required|in:pendiente,en_proceso,completado,cancelado']);
        $data = ['estado' => $request->estado];
        if ($request->estado === 'completado') {
            $data['fecha_realizada'] = now()->toDateString();
            // Si se completa, liberar el dron
            $mantenimiento->dron->update(['estado' => 'disponible']);
        }
        if ($request->estado === 'en_proceso') {
            $mantenimiento->dron->update(['estado' => 'mantenimiento']);
        }
        $mantenimiento->update($data);
        return back()->with('success', 'Estado de mantenimiento actualizado.');
    }
}
