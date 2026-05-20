<?php
namespace App\Http\Controllers;

use App\Models\SkyPass;
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SkyPassController extends Controller
{
    public function index()
    {
        $skypass = auth()->check() ? auth()->user()->skypass : null;
        $planes  = SkyPass::planesDisponibles();
        return view('skypass.index', compact('planes', 'skypass'));
    }

    public function suscribir(Request $request)
    {
        $request->validate(['plan' => 'required|in:mensual,trimestral,anual']);

        $planes = SkyPass::planesDisponibles();
        $plan   = $planes[$request->plan];

        // Crear preferencia MercadoPago
        $accessToken = config('services.mercadopago.access_token');

        $payload = [
            'items' => [[
                'id'          => 'skypass-' . $request->plan,
                'title'       => 'DronShop SkyPass — Plan ' . $plan['label'],
                'quantity'    => 1,
                'unit_price'  => (float) $plan['precio'],
                'currency_id' => 'COP',
            ]],
            'external_reference' => 'SKYPASS-' . auth()->id() . '-' . $request->plan,
            'payer'              => ['email' => auth()->user()->email],
            'back_urls'          => [
                'success' => route('skypass.success'),
                'failure' => route('skypass.failure'),
                'pending' => route('skypass.index'),
            ],
            'auto_return'         => 'approved',
            'binary_mode'         => true,
            'statement_descriptor'=> 'DRONSHOP SKYPASS',
        ];

        $response = Http::withToken($accessToken)
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        if ($response->failed()) {
            Log::error('SkyPass MP Error', ['body' => $response->body()]);
            return back()->with('error', 'Error al procesar el pago. Intenta de nuevo.');
        }

        $data = $response->json();

        // Guardar plan elegido en sesión
        session(['skypass_plan' => $request->plan]);

        return redirect($data['sandbox_init_point']);
    }

    public function success(Request $request)
    {
        $ref  = $request->external_reference; // SKYPASS-{userId}-{plan}
        $parts = explode('-', $ref);

        if (count($parts) < 3 || $parts[0] !== 'SKYPASS') {
            return redirect()->route('skypass.index')->with('error', 'Referencia inválida.');
        }

        $userId = (int) $parts[1];
        $plan   = $parts[2];
        $planes = SkyPass::planesDisponibles();

        if (!isset($planes[$plan]) || $userId !== auth()->id()) {
            return redirect()->route('skypass.index')->with('error', 'Error en la suscripción.');
        }

        $planData = $planes[$plan];
        $inicio   = Carbon::now();
        $fin      = $inicio->copy()->addMonths($planData['meses']);

        // Desactivar suscripciones anteriores
        SkyPass::where('user_id', auth()->id())->update(['activo' => false]);

        SkyPass::create([
            'user_id'       => auth()->id(),
            'plan'          => $plan,
            'precio_pagado' => $planData['precio'],
            'inicio'        => $inicio,
            'vencimiento'   => $fin,
            'activo'        => true,
            'mp_payment_id' => $request->payment_id,
        ]);

        return redirect()->route('skypass.index')
            ->with('success', '🎉 ¡Bienvenido a SkyPass! Tus beneficios están activos.');
    }

    public function failure()
    {
        return redirect()->route('skypass.index')
            ->with('error', 'El pago fue rechazado. Intenta de nuevo.');
    }

    public function cancelar()
    {
        $skypass = auth()->user()->skypass;
        if ($skypass && $skypass->activo) {
            $skypass->update(['activo' => false]);
            return back()->with('success', 'Suscripción cancelada. Tus beneficios siguen hasta el vencimiento.');
        }
        return back()->with('error', 'No tienes una suscripción activa.');
    }
}
