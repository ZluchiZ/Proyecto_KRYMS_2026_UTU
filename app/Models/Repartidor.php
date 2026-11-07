<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Repartidor extends Model
{
    protected $table = 'repartidor';

    protected $primaryKey = 'CI';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'CI', 'Email_Usuario', 'Contraseña', 'Nombre', 'Apellido', 'Teléfono',
        'Estado_Repartidor', 'Foto_Libreta', 'latitud', 'longitud',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'Email_Usuario', 'Email');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'CI_Repartidor', 'CI');
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class, 'CI_Repartidor', 'CI');
    }

    public function pedidosEvaluados(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_repartidor_estado', 'CI_Repartidor', 'N_Pedido', 'CI', 'N_Pedido')
            ->using(PedidoRepartidorEstado::class)
            ->withPivot('Estado');
    }

    public function vehiculo(): HasOne
    {
        return $this->hasOne(Vehiculo::class, 'CI_Repartidor', 'CI');
    }
}