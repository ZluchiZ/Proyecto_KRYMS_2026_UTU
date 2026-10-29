<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrador extends Model
{
    protected $table = 'administrador';

    protected $primaryKey = 'Email_Usuario';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['Email_Usuario'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'Email_Usuario', 'Email');
    }
}