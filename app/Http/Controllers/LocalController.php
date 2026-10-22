<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
    public function updateStatus(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $validated = $request->validate([
            'abierto' => 'required|boolean',
        ]);

        $actualizado = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->update(['Abierto' => $request->boolean('abierto')]);

        abort_unless($actualizado, 404);

        return redirect()
            ->route('dashboard.local')
            ->with('success', $request->boolean('abierto') ? 'El local está abierto.' : 'El local está cerrado.');
    }

    public function storeProducto(Request $request)
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id'), 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'categoria' => [
                'required',
                Rule::in([
                    'Restaurantes',
                    'Supermercados',
                    'Farmacia',
                    'Kioscos',
                    'Bebidas',
                    'Mascotas',
                    'Otros',
                    'Supermercado',
                    'Ferretería',
                    'Rotisería',
                ]),
            ],
            'descripcion' => 'required|string|max:1000',
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'disponible' => 'nullable|boolean',
        ]);

        $comercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Email_Usuario', 'Nombre_Comercio']);

        abort_unless($comercio, 403);

        $identificadorComercio = $comercio->RUT ?? session('usuario_id');
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT asociado.');

        $rutaImagen = $request->file('imagen')->store('productos', 'public');
        DB::table('producto')->insert([
                'RUT_Comercio' => $identificadorComercio,
                'Nombre_Producto' => $validated['nombre'],
                'Precio' => $validated['precio'],
                'Categoria' => $validated['categoria'],
                'Foto_Producto' => Storage::url($rutaImagen),
                'Disponible' => $request->boolean('disponible'),
        ]);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto añadido correctamente.');
    }

    public function updateProducto(Request $request, int $producto): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $validated = $request->validate([
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'descuento' => 'required|numeric|min:0|max:100',
            'disponible' => 'required|boolean',
        ]);

        $comercio = DB::table('comercio')
            ->where('Email_Usuario', session('email'))
            ->first(['RUT']);

        abort_unless($comercio, 403);

        $identificadorComercio = $comercio->RUT ?? session('usuario_id');
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT asociado.');

        $productoQuery = DB::table('producto')
            ->where('ID_Producto', $producto)
            ->where('RUT_Comercio', $identificadorComercio);

        abort_unless($productoQuery->exists(), 404);

        $productoQuery->update([
            'Precio' => $validated['precio'],
            'Descuento_Porcentaje' => $validated['descuento'],
            'Disponible' => $request->boolean('disponible'),
        ]);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroyProducto(int $producto): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $eliminado = DB::table('producto')
            ->where('ID_Producto', $producto)
            ->where('RUT_Comercio', session('usuario_id'))
            ->delete();

        abort_unless($eliminado, 404);

        return redirect()
            ->route('dashboard.local')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function store(Request $request, RegistrationVerificationService $verification)
    {
        $validated = $request->validate([
            'rut' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('comercio', 'RUT'),
            ],
            'nombre' => 'required|string|max:150',
            'nombre_dueno' => 'required|string|max:100',
            'cedula_dueno' => 'required|string|max:20',
            'horario' => ['required', 'string', 'max:255', 'regex:/^([01]\d|2[0-3]):[0-5]\d hrs - ([01]\d|2[0-3]):[0-5]\d hrs$/'],
            'direccion' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'rut.unique' => 'Este RUT ya está registrado. Puedes dejarlo vacío o ingresar otro.',
            'correo.unique' => 'Este correo ya está registrado.',
        ]);

        try {
            $sent = $verification->start('comercio', $validated['correo'], [
                'rut' => $validated['rut'] ?? null,
                'name' => $validated['nombre'],
                'nombre_dueno' => $validated['nombre_dueno'],
                'cedula_dueno' => $validated['cedula_dueno'],
                'horario' => $validated['horario'],
                'direccion' => $validated['direccion'] ?? null,
                'logo' => $validated['logo'] ?? null,
                'password' => Hash::make($validated['contrasena']),
            ]);
        } catch (RegistrationMailDeliveryException $exception) {
            report($exception);

            return back()
                ->withInput($request->except(['contrasena', 'contrasena_confirmation']))
                ->with('error', 'No se pudo enviar el código. Revisá la configuración del correo e intentá nuevamente.');
        }

        return redirect()
            ->route('registration.verify.form', ['email' => $validated['correo']])
            ->with('success', $sent
                ? 'Te enviamos un código de verificación. Ingresalo para completar el registro.'
                : 'Ya hay un registro pendiente para ese correo. Ingresá el código recibido o solicitá uno nuevo.');
    }

}