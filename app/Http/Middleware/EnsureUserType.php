<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        $type = (string) session('tipo_usuario');
        $identifier = session('usuario_id');

        abort_unless(in_array($type, $allowedTypes, true) && filled($identifier), 403);
        abort_unless($this->identityMatches($type, (string) $identifier, session('email')), 403);

        return $next($request);
    }

    private function identityMatches(string $type, string $identifier, ?string $email): bool
    {
        if ($type === 'administrador') {
            return $this->administratorIdentityMatches($identifier, $email);
        }

        $table = match ($type) {
            'cliente' => 'cliente',
            'comercio' => 'comercio',
            'repartidor' => 'repartidor',
            default => null,
        };

        if (! $table || ! Schema::hasTable($table)) {
            return false;
        }

        if ($type === 'comercio') {
            $query = DB::table($table);
            if ($email) {
                $query->where('Email_Usuario', $email);
            } else {
                $query->where(function ($query) use ($identifier): void {
                    $query->where('RUT', $identifier);
                    if (Schema::hasColumn('comercio', 'id')) {
                        $query->orWhere('id', $identifier);
                    }
                });
            }

            $columns = ['RUT', 'Email_Usuario'];
            if (Schema::hasColumn('comercio', 'id')) {
                $columns[] = 'id';
            }
            $account = $query->first($columns);
            if (! $account) {
                return false;
            }

            $identifiers = array_map('strval', array_values(array_filter([
                $account->RUT ?? null,
                $account->Email_Usuario ?? null,
                $account->id ?? null,
            ], static fn ($value): bool => $value !== null)));

            return in_array($identifier, $identifiers, true);
        }

        $account = DB::table($table)->where('CI', $identifier)->first(['Email_Usuario']);

        return $account !== null && (! $email || $account->Email_Usuario === $email);
    }

    private function administratorIdentityMatches(string $identifier, ?string $email): bool
    {
        $email ??= $identifier;
        $hasAdministratorTable = Schema::hasTable('administrador');
        $hasUserTable = Schema::hasTable('usuario');

        if (! $hasAdministratorTable && ! $hasUserTable) {
            return false;
        }

        if ($hasAdministratorTable && DB::table('administrador')
            ->where('Email_Usuario', $email)
            ->exists()) {
            return $identifier === $email;
        }

        return $hasUserTable
            && Schema::hasColumn('usuario', 'Tipo_Usuario')
            && DB::table('usuario')
                ->where('Email', $email)
                ->whereIn(DB::raw('LOWER(Tipo_Usuario)'), ['admin', 'administrador'])
                ->exists()
            && $identifier === $email;
    }
}