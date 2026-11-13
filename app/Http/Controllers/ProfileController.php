<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        [$type, $email, $profile] = $this->currentProfile();
        $vehicle = null;
        $driverLicenseFile = null;
        $identityFile = null;

        if ($type === 'repartidor') {
            $vehicleTable = null;
            foreach (['vehículo', 'Vehículo'] as $table) {
                if (Schema::hasTable($table)) {
                    $vehicleTable = $table;
                    break;
                }
            }

            if ($vehicleTable) {
                $vehicle = DB::table($vehicleTable)
                    ->where('CI_Repartidor', (string) $profile->CI)
                    ->first();
            }

            if (Schema::hasColumn('repartidor', 'Foto_Libreta')) {
                $driverLicenseFile = DB::table('repartidor')
                    ->where('CI', (string) $profile->CI)
                    ->value('Foto_Libreta');
            }

            if (Schema::hasColumn('repartidor', 'Cedula_Archivo')) {
                $identityFile = DB::table('repartidor')
                    ->where('CI', (string) $profile->CI)
                    ->value('Cedula_Archivo');
            }
        }

        return view('profile-edit', [
            'type' => $type,
            'email' => $email,
            'profile' => $profile,
            'fields' => $this->fields($type, $profile),
            'vehicle' => $vehicle,
            'driverLicenseFile' => $driverLicenseFile,
            'identityFile' => $identityFile,
        ]);
    }

    public function update(Request $request)
    {
        [$type, $email, $profile] = $this->currentProfile();
        $fieldDefinitions = $this->fieldDefinitions($type);
        $rules = [];

        foreach ($fieldDefinitions as $name => $definition) {
            $rules[$name] = $definition['rules'];
        }

        if ($type === 'comercio') {
            $rules['logo_url'] = ['nullable', 'url:http,https', 'max:255'];
            $rules['logo'] = ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
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
            }];
        }

        $validated = $request->validate($rules);
        $table = $this->profileTable($type);
        $updates = [];
        $updatedColumns = [];

        foreach ($fieldDefinitions as $name => $definition) {
            foreach ($definition['columns'] as $column) {
                $columnExists = Schema::hasColumn($table, $column);
                $normalizedColumn = strtolower($column);

                if ($columnExists && ! isset($updatedColumns[$normalizedColumn])) {
                    $updates[$column] = $validated[$name] ?? null;
                    $updatedColumns[$normalizedColumn] = true;
                }
            }
        }

        if ($type === 'comercio') {
            $logoValue = $this->resolveCommerceLogoValue($request, $profile);
            if ($request->has('logo_url') || $request->hasFile('logo') || $request->has('logo')) {
                $updates['Logo'] = $logoValue;
                $updatedColumns['logo'] = true;
            }
        }

        DB::transaction(function () use ($table, $email, $updates, $validated): void {
            DB::table($table)
                ->where('Email_Usuario', $email)
                ->update($updates);

            if (isset($validated['nombre'])
                && Schema::hasTable('usuario')
                && Schema::hasColumn('usuario', 'Nombre_de_Usuario')) {
                DB::table('usuario')
                    ->where('Email', $email)
                    ->update(['Nombre_de_Usuario' => $validated['nombre']]);
            }
        });

        return redirect()
            ->route($type === 'cliente' ? 'cliente.profile' : 'profile.edit')
            ->with('success', 'Tu perfil se actualizó correctamente.');
    }

    private function currentProfile(): array
    {
        $type = session('tipo_usuario');
        $email = session('email');

        abort_unless(in_array($type, ['cliente', 'comercio', 'repartidor'], true) && $email, 403);

        $profile = DB::table($this->profileTable($type))
            ->where('Email_Usuario', $email)
            ->first();

        abort_unless($profile, 404);

        return [$type, $email, $profile];
    }

    private function profileTable(string $type): string
    {
        return match ($type) {
            'cliente' => 'cliente',
            'comercio' => 'comercio',
            'repartidor' => 'repartidor',
        };
    }

    private function fieldDefinitions(string $type): array
    {
        return match ($type) {
            'cliente' => [
                'nombre' => [
                    'label' => 'Nombre',
                    'rules' => 'required|string|max:255',
                    'columns' => ['Nombre', 'nombre'],
                ],
                'apellido' => [
                    'label' => 'Apellido',
                    'rules' => 'required|string|max:255',
                    'columns' => ['Apellido', 'apellido'],
                ],
                'telefono' => [
                    'label' => 'Teléfono',
                    'rules' => 'required|string|max:30',
                    'columns' => ['Teléfono', 'telefono'],
                ],
                'direccion' => [
                    'label' => 'Dirección de entrega',
                    'rules' => 'nullable|string|max:500',
                    'columns' => ['Direccion_Entrega', 'direccion'],
                    'type' => 'textarea',
                ],
            ],
            'comercio' => [
                'nombre' => [
                    'label' => 'Nombre del local',
                    'rules' => 'required|string|max:150',
                    'columns' => ['Nombre_Comercio', 'nombre'],
                ],
                'nombre_dueno' => [
                    'label' => 'Nombre del dueño',
                    'rules' => 'required|string|max:100',
                    'columns' => ['Nombre_dueño'],
                ],
                'telefono' => [
                    'label' => 'Teléfono',
                    'rules' => 'nullable|string|max:30',
                    'columns' => ['Teléfono'],
                ],
                'direccion' => [
                    'label' => 'Dirección del local',
                    'rules' => 'nullable|string|max:255',
                    'columns' => ['Dirección', 'direccion'],
                ],
                'horario' => [
                    'label' => 'Horario',
                    'rules' => 'required|string|max:255',
                    'columns' => ['Horario'],
                ],
            ],
            'repartidor' => [
                'nombre' => [
                    'label' => 'Nombre',
                    'rules' => 'required|string|max:100',
                    'columns' => ['Nombre'],
                ],
                'apellido' => [
                    'label' => 'Apellido',
                    'rules' => 'required|string|max:100',
                    'columns' => ['Apellido'],
                ],
                'telefono' => [
                    'label' => 'Teléfono',
                    'rules' => 'required|string|max:30',
                    'columns' => ['Teléfono'],
                ],
            ],
        };
    }

    private function fields(string $type, object $profile): array
    {
        $fields = [];

        foreach ($this->fieldDefinitions($type) as $name => $definition) {
            $column = $definition['columns'][0];
            if ($name === 'direccion' && $type === 'cliente' && isset($profile->direccion)) {
                $column = 'direccion';
            }

            $fields[] = [
                'name' => $name,
                'label' => $definition['label'],
                'type' => $definition['type'] ?? 'text',
                'value' => $profile->{$column} ?? '',
                'required' => str_starts_with($definition['rules'], 'required'),
            ];
        }

        return $fields;
    }

    private function resolveCommerceLogoValue(Request $request, object $profile): ?string
    {
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            if ($file && $file->isValid()) {
                $storedPath = $file->store('comercios/logos', 'public');

                return $storedPath ? Storage::disk('public')->url($storedPath) : null;
            }
        }

        if ($request->has('logo_url')) {
            $value = trim((string) ($request->input('logo_url') ?? ''));

            return $value !== '' ? $value : null;
        }

        if ($request->has('logo')) {
            $value = trim((string) $request->input('logo'));

            return $value !== '' ? $value : null;
        }

        return $profile->Logo ?? null;
    }
}
