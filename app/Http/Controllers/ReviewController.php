<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Comercio;
use App\Models\Resena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function storeCourierReview(Request $request, int $pedido): RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);

        $validated = $request->validate([
            'puntuacion' => 'required|integer|between:1,5',
            'comentario' => 'nullable|string|max:250',
        ]);

        $cliente = Cliente::query()->find(session('usuario_id'));
        abort_unless($cliente, 403);

        DB::transaction(function () use ($validated, $pedido, $cliente): void {
            $pedidoActual = $cliente->pedidos()
                ->where('N_Pedido', $pedido)
                ->lockForUpdate()
                ->first(['N_Pedido', 'Estado', 'CI_Repartidor']);

            abort_unless($pedidoActual, 404, 'El pedido no existe.');
            abort_unless(
                $pedidoActual->Estado === 'entregado' && $pedidoActual->CI_Repartidor,
                409,
                'Solo podés reseñar al repartidor de un pedido entregado.'
            );
            abort_if(
                Resena::query()->where('N_Pedido', $pedido)->exists(),
                409,
                'Ya dejaste una reseña para este pedido.'
            );

            $comentario = trim($validated['comentario'] ?? '');
            Resena::query()->create([
                'CI_Cliente' => $cliente->CI,
                'RUT_Comercio' => null,
                'CI_Repartidor' => $pedidoActual->CI_Repartidor,
                'N_Pedido' => $pedidoActual->N_Pedido,
                'Puntuacion' => $validated['puntuacion'],
                'Comentario' => $comentario === '' ? null : $comentario,
                'Estado_Moderacion' => 'Visible',
                'Motivo_Retiro' => null,
            ]);
        });

        return redirect()
            ->route('cliente.pedidos')
            ->with('success', 'Gracias por reseñar a tu repartidor.');
    }

    public function store(Request $request, string $rut): RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);

        $validated = $request->validate([
            'puntuacion' => 'required|integer|between:0,5',
            'comentario' => 'nullable|string|max:250',
        ]);

        $comercio = Comercio::query()->where('RUT', $rut)->exists();
        abort_unless($comercio, 404);

        $cliente = Cliente::query()->find(session('usuario_id'));
        abort_unless($cliente, 403);

        $comentario = trim($validated['comentario'] ?? '');
        $datosResena = [
            'Puntuacion' => $validated['puntuacion'],
            'Comentario' => $comentario === '' ? null : $comentario,
        ];

        $resena = $cliente->resenas()
            ->where('RUT_Comercio', $rut)
            ->first();

        if ($resena) {
            $resena->update($datosResena);
        } else {
            $cliente->resenas()->create($datosResena + [
                'RUT_Comercio' => $rut,
                'CI_Repartidor' => null,
                'Estado_Moderacion' => 'Visible',
                'Motivo_Retiro' => null,
            ]);
        }

        return redirect()
            ->route('local.profile', $rut)
            ->with('success', $resena ? 'Tu reseña fue actualizada.' : 'Gracias por dejar tu reseña.');
    }
}