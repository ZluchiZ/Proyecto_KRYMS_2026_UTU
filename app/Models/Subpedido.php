<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subpedido extends Model
{
    protected $table = 'subpedido';

    protected $primaryKey = 'N_Subpedido';

    public $timestamps = false;

    protected $fillable = [
        'N_Pedido', 'RUT_Comercio', 'Fecha', 'Hora', 'Tiempo_estimado',
        'Monto_Total', 'Estado',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'N_Pedido', 'N_Pedido');
    }

    public function comercio(): BelongsTo
    {
        return $this->belongsTo(Comercio::class, 'RUT_Comercio', 'RUT');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_de_pedido', 'N_Subpedido', 'ID_Producto', 'N_Subpedido', 'ID_Producto')
            ->using(DetalleDePedido::class)
            ->withPivot('Cantidad');
    }
}