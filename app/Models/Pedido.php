<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedido';

    protected $primaryKey = 'N_Pedido';

    public $timestamps = false;

    protected $fillable = [
        'CI_Cliente', 'CI_Repartidor', 'ID_Tarjeta', 'Fecha', 'Hora', 'Estado',
        'Ubicacion', 'Costo_de_envio', 'Monto_Total', 'Confirmacion_entrega',
        'Tiempo_estimado', 'Metodo_de_pago', 'latitud', 'longitud',
        'Distancia_Local_Km', 'Estado_Entrega', 'Codigo_Secreto',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CI_Cliente', 'CI');
    }

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Repartidor::class, 'CI_Repartidor', 'CI');
    }

    public function tarjeta(): BelongsTo
    {
        return $this->belongsTo(Tarjeta::class, 'ID_Tarjeta', 'ID');
    }

    public function subpedidos(): HasMany
    {
        return $this->hasMany(Subpedido::class, 'N_Pedido', 'N_Pedido');
    }

    public function repartidoresEvaluados(): BelongsToMany
    {
        return $this->belongsToMany(Repartidor::class, 'pedido_repartidor_estado', 'N_Pedido', 'CI_Repartidor', 'N_Pedido', 'CI')
            ->using(PedidoRepartidorEstado::class)
            ->withPivot('Estado');
    }

    public function resena(): HasOne
    {
        return $this->hasOne(Resena::class, 'N_Pedido', 'N_Pedido');
    }
}