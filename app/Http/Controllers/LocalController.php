<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
=======
use App\Exceptions\RegistrationMailDeliveryException;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
<<<<<<< HEAD
    private function geocode(?string $address): array
    {
        if (! $address) {
            return ['latitud' => null, 'longitud' => null];
        }

        try {
            $result = Http::withHeaders([
                'User-Agent' => 'KRYMS Proyecto 2026 / Laravel',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address.', Uruguay',
                'format' => 'json',
                'limit' => 1,
            ])->json();

            return [
                'latitud' => isset($result[0]['lat']) ? (float) $result[0]['lat'] : null,
                'longitud' => isset($result[0]['lon']) ? (float) $result[0]['lon'] : null,
            ];
        } catch (\Throwable) {
            return ['latitud' => null, 'longitud' => null];
        }
    }

=======
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
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

<<<<<<< HEAD
    public function store(Request $request)
=======
    public function store(Request $request, RegistrationVerificationService $verification)
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
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
<<<<<<< HEAD
            $password = bcrypt($validated['contrasena']);
            $coordenadas = $this->geocode($validated['direccion'] ?? null);

            $request->session()->put('pending_email_verification', $validated['correo']);

            try {
                app(EmailVerificationService::class)->start($validated['correo'], [
                    'type' => 'comercio',
                    'data' => [
                        'rut' => $validated['rut'] ?? null,
                        'nombre' => $validated['nombre'],
                        'nombre_dueno' => $validated['nombre_dueno'],
                        'cedula_dueno' => $validated['cedula_dueno'],
                        'horario' => $validated['horario'],
                        'direccion' => $validated['direccion'] ?? null,
                        'logo' => $validated['logo'] ?? null,
                        'latitud' => $coordenadas['latitud'],
                        'longitud' => $coordenadas['longitud'],
                        'password' => $password,
                    ],
                ]);
            } catch (\Throwable $e) {
                report($e);

                return redirect()
                    ->route('email-verification.show')
                    ->with('error', 'No se pudo enviar el código. Revisá la configuración del correo y volvé a intentar el reenvío.');
            }

            return redirect()
                ->route('email-verification.show')
                ->with('success', 'Registro pendiente. Confirmá tu correo para guardar la solicitud del local.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo iniciar el registro. Intentá nuevamente.');
        }
    }


=======
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

>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
}