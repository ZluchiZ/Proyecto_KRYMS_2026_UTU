<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $table = 'reporte';

    protected $primaryKey = 'ID_Reporte';

    public $timestamps = false;

    protected $fillable = [
        'CI_Cliente', 'RUT_Comercio', 'Categoria', 'Comentario',
        'Estado_Gestion', 'Resolucion',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CI_Cliente', 'CI');
    }

    public function comercio(): BelongsTo
    {
        return $this->belongsTo(Comercio::class, 'RUT_Comercio', 'RUT');
    }
}