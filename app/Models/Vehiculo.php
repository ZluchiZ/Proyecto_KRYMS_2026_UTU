<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehiculo extends Model
{
    protected $table = 'vehículo';

    protected $primaryKey = 'Matricula';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'Matricula', 'CI_Repartidor', 'Modelo', 'Color',
        'Libreta_de_propiedad', 'Seguro', 'Foto',
    ];

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Repartidor::class, 'CI_Repartidor', 'CI');
    }
}