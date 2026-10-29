<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PedidoRepartidorEstado extends Pivot
{
    protected $table = 'pedido_repartidor_estado';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['N_Pedido', 'CI_Repartidor', 'Estado'];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'N_Pedido', 'N_Pedido');
    }

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Repartidor::class, 'CI_Repartidor', 'CI');
    }
}