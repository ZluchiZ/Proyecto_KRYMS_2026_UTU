<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'cliente';

    protected $primaryKey = 'CI';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'CI', 'Email_Usuario', 'Contraseña', 'Nombre', 'Apellido', 'Teléfono',
        'fecha_nacimiento', 'Direccion_Entrega', 'direccion', 'latitud', 'longitud',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'Email_Usuario', 'Email');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'CI_Cliente', 'CI');
    }

    public function tarjetas(): HasMany
    {
        return $this->hasMany(Tarjeta::class, 'CI_Cliente', 'CI');
    }

    public function carritos(): HasMany
    {
        return $this->hasMany(Carrito::class, 'CI_Cliente', 'CI');
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class, 'CI_Cliente', 'CI');
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'CI_Cliente', 'CI');
    }
}