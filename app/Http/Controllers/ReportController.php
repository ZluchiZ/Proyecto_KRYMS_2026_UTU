<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index()
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);

        $cliente = Cliente::query()->find(session('usuario_id'));
        abort_unless($cliente, 404);

        $reportes = $cliente->reportes()
            ->orderByDesc('ID_Reporte')
            ->get();

        return view('Cliente.Reportes', [
            'cliente' => $cliente,
            'reportes' => $reportes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'cliente' && session('usuario_id'), 403);

        $validated = $request->validate([
            'categoria' => ['required', Rule::in(['local', 'repartidor', 'aplicacion', 'administracion', 'otro'])],
            'comentario' => 'required|string|max:500',
        ], [
            'categoria.required' => 'Seleccioná una categoría para el reporte.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'comentario.required' => 'Escribí el detalle del reporte.',
            'comentario.max' => 'El reporte no puede superar los 500 caracteres.',
        ]);

        $cliente = Cliente::query()->find(session('usuario_id'));
        abort_unless($cliente, 403);

        $cliente->reportes()->create([
            'RUT_Comercio' => null,
            'Categoria' => $validated['categoria'],
            'Comentario' => trim($validated['comentario']),
            'Estado_Gestion' => 'Pendiente',
            'Resolucion' => null,
        ]);

        return redirect()
            ->route('cliente.reportes.index')
            ->with('success', 'Tu reporte fue enviado y quedó pendiente de revisión.');
    }
}