<?php
namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    private string $accessToken;
    private string $baseUrl;

    public function __construct()
    {
        // En .env: MERCADOPAGO_ACCESS_TOKEN=TEST-xxxxxxxx
        $this->accessToken = config('services.mercadopago.access_token');
        $this->baseUrl     = 'https://api.mercadopago.com';
    }

    public function crearPreferencia(Orden $orden)
    {
        abort_if($orden->user_id !== auth()->id(), 403);

        if ($orden->estado !== 'pendiente') {
            return response()->json(['error' => 'Orden no está pendiente.'], 422);
        }

        $items = $orden->items->map(fn($item) => [
            'id'          => (string) $item->producto_id,
            'title'       => $item->nombre_producto,
            'quantity'    => $item->cantidad,
            'unit_price'  => (float) $item->precio_unitario,
            'currency_id' => 'COP',
        ])->toArray();

        // Costo de envío como ítem extra
        $items[] = [
            'id'         => 'envio',
            'title'      => 'Costo de envío (' . ucfirst($orden->transporte) . ')',
            'quantity'   => 1,
            'unit_price' => (float) $orden->costo_envio,
            'currency_id'=> 'COP',
        ];

        $payload = [
            'items'               => $items,
            'external_reference'  => $orden->codigo,
            'payer'               => [
                'email' => auth()->user()->email,
                'name'  => auth()->user()->name,
            ],
            'back_urls' => [
                'success' => route('pago.success'),
                'failure' => route('pago.failure'),
                'pending' => route('pago.pending'),
            ],
            'auto_return'         => 'approved',
            'notification_url'    => route('pago.webhook'),
            'statement_descriptor'=> 'DRONSHOP',
        ];

        $response = Http::withToken($this->accessToken)
            ->post("{$this->baseUrl}/checkout/preferences", $payload);

        if ($response->failed()) {
            Log::error('MP Error', ['body' => $response->body()]);
            return response()->json(['error' => 'Error con MercadoPago.'], 500);
        }

        $data = $response->json();

        $orden->update(['mp_preference_id' => $data['id']]);

        return response()->json([
            'preference_id' => $data['id'],
            'sandbox_url'   => $data['sandbox_init_point'],
            'url'           => $data['init_point'],
        ]);
    }

    public function success(Request $request)
    {
        $orden = Orden::where('codigo', $request->external_reference)->first();

        if ($orden) {
            $orden->update([
                'estado'         => 'pagado',
                'mp_payment_id'  => $request->payment_id,
                'mp_status'      => $request->status,
            ]);
        }

        return redirect()->route('orden.show', $orden)
            ->with('success', '🎉 ¡Pago exitoso! Tu orden está confirmada.');
    }

    public function failure(Request $request)
    {
        $orden = Orden::where('codigo', $request->external_reference)->first();
        return redirect()->route('orden.pago', $orden)
            ->with('error', 'El pago fue rechazado. Intenta de nuevo.');
    }

    public function pending(Request $request)
    {
        $orden = Orden::where('codigo', $request->external_reference)->first();
        return redirect()->route('orden.show', $orden)
            ->with('info', 'Tu pago está pendiente de confirmación.');
    }

    public function webhook(Request $request)
    {
        if ($request->type === 'payment') {
            $paymentId = $request->input('data.id');

            $response = Http::withToken($this->accessToken)
                ->get("{$this->baseUrl}/v1/payments/{$paymentId}");

            if ($response->ok()) {
                $payment = $response->json();
                $orden   = Orden::where('codigo', $payment['external_reference'])->first();

                if ($orden) {
                    $nuevoEstado = match($payment['status']) {
                        'approved' => 'pagado',
                        'rejected' => 'pendiente',
                        default    => $orden->estado,
                    };
                    $orden->update([
                        'estado'        => $nuevoEstado,
                        'mp_payment_id' => $paymentId,
                        'mp_status'     => $payment['status'],
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
