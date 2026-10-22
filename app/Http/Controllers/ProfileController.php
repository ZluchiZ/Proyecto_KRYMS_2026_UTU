<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    public function edit()
    {
        [$type, $email, $profile] = $this->currentProfile();

        return view('profile-edit', [
            'type' => $type,
            'email' => $email,
            'fields' => $this->fields($type, $profile),
        ]);
    }

    public function update(Request $request)
    {
        [$type, $email] = $this->currentProfile();
        $fieldDefinitions = $this->fieldDefinitions($type);
        $rules = [];

        foreach ($fieldDefinitions as $name => $definition) {
            $rules[$name] = $definition['rules'];
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
}
