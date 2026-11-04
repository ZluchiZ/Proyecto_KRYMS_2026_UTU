<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resena extends Model
{
    protected $table = 'reseña';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'CI_Cliente', 'RUT_Comercio', 'CI_Repartidor', 'N_Pedido',
        'Puntuacion', 'Comentario', 'Estado_Moderacion', 'Motivo_Retiro',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CI_Cliente', 'CI');
    }

    public function comercio(): BelongsTo
    {
        return $this->belongsTo(Comercio::class, 'RUT_Comercio', 'RUT');
    }

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Repartidor::class, 'CI_Repartidor', 'CI');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'N_Pedido', 'N_Pedido');
    }
}