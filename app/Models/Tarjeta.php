<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarjeta extends Model
{
    protected $table = 'tarjeta';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = ['CI_Cliente', 'Banco'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CI_Cliente', 'CI');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'ID_Tarjeta', 'ID');
    }
}