<?php

namespace App\Http\Controllers\wompi\webhook; // Ajustado a tu estructura de carpetas 'wompi'

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Suscripcion;
use App\Models\Empresa;
use Exception;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Http;

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

            // Variable para controlar si disparamos la alerta de correo a la App Web
            $notificarCambioApp = false;

            if ($statusWompi === 'APPROVED') {
                // PAGO EXITOSO: Cambiamos a Estado 13 (Para tu activación manual)
                $suscripcion->id_estado_suscripcion = 13;
                $suscripcion->observaciones_suscripcion = "Pago aprobado en Wompi(Asíncrono). ID: " . $idTransaccion;
                $suscripcion->save();

                if ($empresa) {
                    $empresa->id_estado = 13;
                    $empresa->save();
                }

                $notificarCambioApp = true;

            } elseif (in_array($statusWompi, ['DECLINED', 'VOIDED', 'ERROR'])) {
        
                // PAGO FALLIDO DEFINITIVO: Estado 14
                $suscripcion->id_estado_suscripcion = 14;
                $suscripcion->observaciones_suscripcion = "Pago fallido en Wompi ($statusWompi) (Asíncrono). ID: " . $idTransaccion;
                $suscripcion->save();
        
                if ($empresa) {
                    $empresa->id_estado = 14;
                    $empresa->save();
                }

                $notificarCambioApp = true;
        
            } elseif ($statusWompi === 'PENDING') {
                
                // PAGO EN PROCESO (PSE / Tarjetas en validación): Estado 15
                $suscripcion->id_estado_suscripcion = 15;
                $suscripcion->observaciones_suscripcion = "Pago en verificación asíncrona (PSE/Crédito). ID: " . $idTransaccion;
                $suscripcion->save();
                
                if ($empresa) {
                    $empresa->id_estado = 15;
                    $empresa->save();
                }

                $notificarCambioApp = true;
            }

            // ======================================================================
            // NOTIFICAR A LA APP WEB PARA DISPARAR CORREOS
            // ======================================================================
            if ($notificarCambioApp) {
                try {
                    // Url de tu Landing/App Web (ej: https://storedimoapp.com/api/wompi-notificar-correo)
                    $urlAppWeb = config('services.app_web.url') . '/api/wompi-notificar-correo';

                    $client = new \GuzzleHttp\Client();
                    $client->post($urlAppWeb, [
                        'headers' => [
                            'X-Storedimo-Token' => config('services.app_web.internal_token'),
                            'Content-Type'      => 'application/json',
                        ],
                        'json' => [
                            'id_suscripcion' => $idSuscripcion,
                            'id_transaccion' => $idTransaccion,
                            'estado_wompi'   => $statusWompi
                        ]
                    ]);
                    
                    Log::info("Notificación de correo enviada a la App Web para la suscripción: " . $idSuscripcion);

                } catch (Exception $eMail) {
                    // Capturamos el error por si la App Web está caída, para que no rompa el webhook de Wompi (200)
                    Log::error('No se pudo comunicar con la App Web para enviar el correo: ' . $eMail->getMessage());
                }
            }
            // ======================================================================

            // Obligatorio responderle 200 a Wompi para que no siga intentando enviar el mismo cobro
            return response()->json(['success' => true, 'message' => 'Procesado correctamente'], 200);

        } catch (Exception $e) {
            Log::error('Error en Webhook Wompi: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}
