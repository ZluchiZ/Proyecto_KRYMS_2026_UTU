<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrito extends Model
{
    protected $table = 'carrito';

    protected $primaryKey = 'ID_Carrito';

    protected $fillable = ['CI_Cliente', 'ID_Producto', 'Cantidad'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CI_Cliente', 'CI');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ID_Producto', 'ID_Producto');
    }
}