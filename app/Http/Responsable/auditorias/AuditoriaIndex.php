<?php

namespace App\Http\Responsable\auditorias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditoriaIndex implements Responsable
{
    public function toResponse($request)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            // Empresa 5 y rol 3 = Softdimo. Solo ese caso ve todas las empresas.
            $esSoftdimo = (int) $usuario->id_empresa === 5 && (int) $usuario->id_rol === 3;

            $porPagina = (int) $request->input('length', 25);
            $inicio = (int) $request->input('start', 0);

            if ($porPagina < 1) {
                $porPagina = 25;
            }
            if ($porPagina > 100) {
                $porPagina = 100;
            }
            if ($inicio < 0) {
                $inicio = 0;
            }

            $query = Audit::query()
                ->leftJoin('usuarios', 'usuarios.id_usuario', '=', 'audits.user_id')
                ->leftJoin('empresas', 'empresas.id_empresa', '=', 'usuarios.id_empresa')
                ->select(
                    'audits.id',
                    'audits.user_id',
                    DB::raw("TRIM(CONCAT(COALESCE(usuarios.nombre_usuario, ''), ' ', COALESCE(usuarios.apellido_usuario, ''))) as nombre_usuario"),
                    'empresas.nombre_empresa',
                    'audits.event',
                    'audits.auditable_type',
                    'audits.auditable_id',
                    'audits.old_values',
                    'audits.new_values',
                    'audits.url',
                    'audits.ip_address',
                    'audits.user_agent',
                    'audits.tags',
                    'audits.created_at'
                )
                ->orderByDesc('audits.id');

            if (!$esSoftdimo) {
                $query->where('usuarios.id_empresa', $usuario->id_empresa);
            }

            $total = (clone $query)->count();
            $filas = $query->skip($inicio)->take($porPagina)->get();

            // return response()->json($query->get());

            return response()->json([
                'draw'            => (int) $request->input('draw'),
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $filas,
            ]);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
