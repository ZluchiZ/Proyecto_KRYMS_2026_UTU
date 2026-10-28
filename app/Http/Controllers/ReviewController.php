<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request, string $rut): RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);

        $validated = $request->validate([
            'puntuacion' => 'required|integer|between:0,5',
            'comentario' => 'nullable|string|max:250',
        ]);

        $comercio = DB::table('comercio')->where('RUT', $rut)->exists();
        abort_unless($comercio, 404);

        $cliente = DB::table('cliente')->where('CI', session('usuario_id'))->exists();
        abort_unless($cliente, 403);

        $comentario = trim($validated['comentario'] ?? '');
        $datosResena = [
            'Puntuacion' => $validated['puntuacion'],
            'Comentario' => $comentario === '' ? null : $comentario,
        ];

        $resenaExistente = DB::table('reseña')
            ->where('CI_Cliente', session('usuario_id'))
            ->where('RUT_Comercio', $rut)
            ->exists();

        if ($resenaExistente) {
            DB::table('reseña')
                ->where('CI_Cliente', session('usuario_id'))
                ->where('RUT_Comercio', $rut)
                ->update($datosResena);
        } else {
            DB::table('reseña')->insert($datosResena + [
                'CI_Cliente' => session('usuario_id'),
                'RUT_Comercio' => $rut,
                'CI_Repartidor' => null,
                'Estado_Moderacion' => 'Visible',
                'Motivo_Retiro' => null,
            ]);
        }

        return redirect()
            ->route('local.profile', $rut)
            ->with('success', $resenaExistente ? 'Tu reseña fue actualizada.' : 'Gracias por dejar tu reseña.');
    }
}