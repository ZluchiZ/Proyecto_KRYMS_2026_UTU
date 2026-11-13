<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationMailDeliveryException;
use App\Models\Comercio;
use App\Services\RegistrationVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
    public function updateStatus(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $validated = $request->validate([
            'abierto' => 'required|boolean',
        ]);

        $actualizado = Comercio::query()
            ->where('Email_Usuario', session('email'))
            ->update(['Abierto' => $request->boolean('abierto')]);

        abort_unless($actualizado, 404);

        return redirect()
            ->route('dashboard.local')
            ->with('success', $request->boolean('abierto') ? 'El local está abierto.' : 'El local está cerrado.');
    }

    public function salesHistory()
    {
        abort_unless(session('tipo_usuario') === 'comercio' && session('usuario_id') && session('email'), 403);

        $comercio = Comercio::query()
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Nombre_Comercio']);

        abort_unless($comercio, 403);

        $ventas = DB::table('subpedido')
            ->join('pedido', 'subpedido.N_Pedido', '=', 'pedido.N_Pedido')
            ->leftJoin('cliente', 'pedido.CI_Cliente', '=', 'cliente.CI')
            ->where('subpedido.RUT_Comercio', $comercio->RUT)
            ->select(
                'subpedido.N_Subpedido',
                'pedido.N_Pedido',
                'pedido.Fecha',
                'pedido.Hora',
                'pedido.Ubicacion',
                'pedido.Metodo_de_pago',
                'subpedido.Estado',
                'subpedido.Monto_Total as Total_Local',
                'cliente.Nombre as Nombre_Cliente',
                'cliente.Apellido as Apellido_Cliente',
                'cliente.Email_Usuario as Correo_Cliente'
            )
            ->orderByDesc('pedido.Fecha')
            ->orderByDesc('pedido.Hora')
            ->orderByDesc('subpedido.N_Subpedido')
            ->paginate(10);

        $productosPorSubpedido = collect();
        if ($ventas->isNotEmpty()) {
            $productosPorSubpedido = DB::table('detalle_de_pedido')
                ->join('producto', 'detalle_de_pedido.ID_Producto', '=', 'producto.ID_Producto')
                ->whereIn('detalle_de_pedido.N_Subpedido', $ventas->getCollection()->pluck('N_Subpedido'))
                ->select('detalle_de_pedido.N_Subpedido', 'producto.Nombre_Producto', 'detalle_de_pedido.Cantidad')
                ->orderBy('producto.Nombre_Producto')
                ->get()
                ->groupBy('N_Subpedido');
        }

        return view('Local.HistorialVentas', [
            'comercio' => $comercio,
            'ventas' => $ventas,
            'productosPorSubpedido' => $productosPorSubpedido,
        ]);
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

        $comercio = Comercio::query()
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Email_Usuario', 'Nombre_Comercio']);

        $identificadorComercio = $this->resolveComercioIdentifier($comercio);
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT o identificador asociado.');

        $rutaImagen = $request->file('imagen')->store('productos', 'public');
        $comercio->productos()->create([
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

        $comercio = Comercio::query()
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Email_Usuario']);

        $identificadorComercio = $this->resolveComercioIdentifier($comercio);
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT o identificador asociado.');

        $productoLocal = $comercio->productos()->where('ID_Producto', $producto)->first();
        abort_unless($productoLocal, 404);

        $productoLocal->update([
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

        $comercio = Comercio::query()
            ->where('Email_Usuario', session('email'))
            ->first(['RUT', 'Email_Usuario']);

        $identificadorComercio = $this->resolveComercioIdentifier($comercio);
        abort_unless($identificadorComercio, 403, 'Este comercio no tiene un RUT o identificador asociado.');

        $eliminado = $comercio->productos()->where('ID_Producto', $producto)->delete();

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
            'horario' => ['nullable', 'string', 'max:255', 'regex:/^(([01]\d|2[0-3]):[0-5]\d\s*(hrs|hr|h)?\s*( - |-|a|al)\s*([01]\d|2[0-3]):[0-5]\d\s*(hrs|hr|h)?)|(([01]\d|2[0-3]):[0-5]\d\s*-\s*([01]\d|2[0-3]):[0-5]\d)$/'],
            'horario_inicio' => ['nullable', 'date_format:H:i'],
            'horario_fin' => ['nullable', 'date_format:H:i'],
            'direccion' => 'nullable|string|max:255',
            'logo' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! request()->hasFile('logo')) {
                        if ($value === null || trim((string) $value) === '') {
                            return;
                        }

                        if (! filter_var(trim((string) $value), FILTER_VALIDATE_URL)
                            || ! in_array(parse_url(trim((string) $value), PHP_URL_SCHEME), ['http', 'https'], true)) {
                            $fail('El logo debe ser una URL válida o una imagen JPG, JPEG, PNG o WEBP.');
                        }

                        return;
                    }

                    $file = request()->file('logo');
                    if (! $file || ! $file->isValid()) {
                        $fail('El archivo del logo no es válido.');
                        return;
                    }

                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
                        $fail('El logo debe ser una imagen JPG, JPEG, PNG o WEBP.');
                    }
                },
            ],
            'logo_url' => ['nullable', 'url:http,https', 'max:255'],
            'correo' => [
                'required',
                'email:rfc',
                'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
                'max:255',
                Rule::unique('usuario', 'Email'),
            ],
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9\s]/',
                'confirmed',
            ],
        ], [
            'rut.unique' => 'Este RUT ya está registrado. Puedes dejarlo vacío o ingresar otro.',
            'horario_inicio.date_format' => 'El horario de inicio debe tener formato HH:MM.',
            'horario_fin.date_format' => 'El horario de cierre debe tener formato HH:MM.',
            'correo.unique' => 'Este correo ya está registrado.',
            'correo.regex' => 'Ingresá un correo válido, por ejemplo nombre@gmail.com.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contrasena.regex' => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un símbolo.',
        ]);

        $logoValue = $this->resolveLogoValue($request);

        $horario = $validated['horario'] ?? null;
        if (($request->filled('horario_inicio') || $request->filled('horario_fin')) && $request->filled('horario_inicio') && $request->filled('horario_fin')) {
            $horario = $request->input('horario_inicio').' - '.$request->input('horario_fin');
        }

        try {
            $sent = $verification->start('comercio', $validated['correo'], [
                'rut' => $validated['rut'] ?? null,
                'name' => $validated['nombre'],
                'nombre_dueno' => $validated['nombre_dueno'],
                'cedula_dueno' => $validated['cedula_dueno'],
                'horario' => $horario,
                'direccion' => $validated['direccion'] ?? null,
                'logo' => $logoValue,
                'password' => $validated['contrasena'],
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

    private function resolveComercioIdentifier(?Comercio $comercio): ?string
    {
        if (! $comercio) {
            return null;
        }

        if (! empty($comercio->RUT)) {
            return (string) $comercio->RUT;
        }

        $base = $comercio->Email_Usuario ?? $comercio->Email_Usuario ?? 'local';
        $identifier = preg_replace('/[^a-zA-Z0-9]/', '', strtolower((string) $base));
        $identifier = substr($identifier ?: 'local', 0, 20);

        if ($identifier === '') {
            return null;
        }

        $comercio->RUT = $identifier;
        $comercio->save();

        return $identifier;
    }

    private function resolveLogoValue(Request $request): ?string
    {
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            if ($file && $file->isValid()) {
                $storedPath = $file->store('comercios/logos', 'public');

                return $storedPath ? Storage::disk('public')->url($storedPath) : null;
            }
        }

        $logoValue = $request->input('logo_url') ?? $request->input('logo');
        $trimmedLogo = is_string($logoValue) ? trim($logoValue) : '';

        return $trimmedLogo !== '' ? $trimmedLogo : null;
    }
}
