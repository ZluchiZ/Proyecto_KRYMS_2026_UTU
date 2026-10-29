<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    protected $table = 'producto';

    protected $primaryKey = 'ID_Producto';

    public $timestamps = false;

    protected $fillable = [
        'RUT_Comercio', 'Nombre_Producto', 'Categoria', 'Precio',
        'Descuento_Porcentaje', 'Foto_Producto', 'Disponible',
    ];

    protected function casts(): array
    {
        return ['Disponible' => 'boolean'];
    }

    public function comercio(): BelongsTo
    {
        return $this->belongsTo(Comercio::class, 'RUT_Comercio', 'RUT');
    }

    public function subpedidos(): BelongsToMany
    {
        return $this->belongsToMany(Subpedido::class, 'detalle_de_pedido', 'ID_Producto', 'N_Subpedido', 'ID_Producto', 'N_Subpedido')
            ->using(DetalleDePedido::class)
            ->withPivot('Cantidad');
    }
}