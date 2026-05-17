<?php

namespace App\Http\Controllers\wompi\webhook; // Ajustado a tu estructura de carpetas 'wompi'

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Suscripcion;
use App\Models\Empresa;
use Exception;
use Illuminate\Support\Facades\Log;

class WompiWebhookController extends Controller
{
    public function procesarNotificacion(Request $request)
    {
        // 1. Capturar el JSON completo que envía Wompi
        $payload = $request->all();
        Log::info('Wompi Webhook Recibido:', $payload);

        // 2. Validar que el evento sea de actualización de transacción
        if (!isset($payload['event']) || $payload['event'] !== 'transaction.updated') {
            return response()->json(['message' => 'Evento ignorado'], 200);
        }

        // 3. VALIDACIÓN DE SEGURIDAD: Verificar la firma del Webhook
        // Wompi envía una firma en el JSON para asegurar que nadie suplante su identidad
        if (!isset($payload['signature']['checksum']) || !isset($payload['timestamp'])) {
            return response()->json(['error' => 'Firma ausente'], 400);
        }

        $transaction = $payload['data']['transaction'];
        
        // La fórmula oficial de Wompi para validar el webhook es:
        // SHA256( id_transaccion + estado + valor_centavos + timestamp + secreto_eventos )
        $idTransaccion = $transaction['id'];
        $statusWompi   = $transaction['status'];
        $montoCentavos = $transaction['amount_in_cents'];
        $timestamp     = $payload['timestamp'];
        // $secretoEventos = env('WOMPI_EVENTS_SECRET');
        $secretoEventos = config('services.wompi.events_secret');

        $cadenaLocal = $idTransaccion . $statusWompi . $montoCentavos . $timestamp . $secretoEventos;
        $firmaLocal  = hash('sha256', $cadenaLocal);

        // Si la firma que calculamos no es igual a la que envió Wompi, rechazamos por seguridad
        if ($firmaLocal !== $payload['signature']['checksum']) {
            Log::warning('¡Alerta de seguridad! Firma de Webhook Wompi inválida.');
            return response()->json(['error' => 'Firma inválida'], 403);
        }

        // 4. PROCESAR EL NEGOCIO (Ya estamos 100% seguros de que es Wompi)
        $referencia = $transaction['reference']; // Ej: "STOR-5-1715783200"
        $partesReferencia = explode('-', $referencia);
        
        if (count($partesReferencia) < 2) {
            return response()->json(['error' => 'Formato de referencia inválido'], 400);
        }
        
        $idSuscripcion = $partesReferencia[1]; // ID real de la suscripción

        try {
            $suscripcion = Suscripcion::find($idSuscripcion);

            if (!$suscripcion) {
                return response()->json(['error' => 'Suscripción no encontrada'], 404);
            }

            $empresa = Empresa::find($suscripcion->id_empresa_suscrita);

            if ($statusWompi === 'APPROVED') {
                // PAGO EXITOSO: Cambiamos a Estado 13 (Para tu activación manual)
                $suscripcion->id_estado_suscripcion = 13;
                $suscripcion->observaciones_suscripcion = "Pago aprobado en Wompi. ID: " . $idTransaccion;
                $suscripcion->save();

                if ($empresa) {
                    $empresa->id_estado = 13;
                    $empresa->save();
                }
            } else {
                // PAGO FALLIDO / RECHAZADO: Cambiamos al nuevo Estado 14 (Falla de pago)
                $suscripcion->id_estado_suscripcion = 14;
                $suscripcion->observaciones_suscripcion = "Pago fallido en Wompi ($statusWompi). ID: " . $idTransaccion;
                $suscripcion->save();

                if ($empresa) {
                    $empresa->id_estado = 14;
                    $empresa->save();
                }
            }

            // Obligatorio responderle 200 a Wompi para que no siga intentando enviar el mismo cobro
            return response()->json(['success' => true, 'message' => 'Procesado'], 200);

        } catch (Exception $e) {
            Log::error('Error en Webhook Wompi: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}
