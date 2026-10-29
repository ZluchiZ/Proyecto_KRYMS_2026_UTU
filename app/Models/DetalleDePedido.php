<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DetalleDePedido extends Pivot
{
    protected $table = 'detalle_de_pedido';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['N_Subpedido', 'ID_Producto', 'Cantidad'];

    public function subpedido(): BelongsTo
    {
        return $this->belongsTo(Subpedido::class, 'N_Subpedido', 'N_Subpedido');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ID_Producto', 'ID_Producto');
    }
}