<?php

namespace App\Providers;

use App\Models\Usuario;
use OwenIt\Auditing\Contracts\UserResolver;

class ApiAuditUserResolver implements UserResolver
{
    public static function resolve()
    {
        $id = request()->input('id_audit');

        if ($id && is_numeric($id)) {
            return Usuario::find($id);
        }

        return null;
    }
}
