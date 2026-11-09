<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comercio extends Model
{
    protected $table = 'comercio';

    protected $primaryKey = 'Email_Usuario';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'Email_Usuario', 'RUT', 'Contraseña', 'Nombre_Comercio', 'Teléfono',
        'Dirección', 'Logo', 'Nombre_dueño', 'CI_Dueño', 'Horario', 'Abierto',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'Email_Usuario', 'Email');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'RUT_Comercio', 'RUT');
    }

    public function subpedidos(): HasMany
    {
        return $this->hasMany(Subpedido::class, 'RUT_Comercio', 'RUT');
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class, 'RUT_Comercio', 'RUT');
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'RUT_Comercio', 'RUT');
    }
}