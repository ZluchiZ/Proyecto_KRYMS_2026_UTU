<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $primaryKey = 'Email';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['Email', 'Nombre_de_Usuario', 'Contraseña', 'Tipo_Usuario'];

    protected $hidden = ['Contraseña'];

    public function getAuthPassword(): string
    {
        return (string) $this->{'Contraseña'};
    }

    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class, 'Email_Usuario', 'Email');
    }

    public function comercio(): HasOne
    {
        return $this->hasOne(Comercio::class, 'Email_Usuario', 'Email');
    }

    public function repartidor(): HasOne
    {
        return $this->hasOne(Repartidor::class, 'Email_Usuario', 'Email');
    }

    public function administrador(): HasOne
    {
        return $this->hasOne(Administrador::class, 'Email_Usuario', 'Email');
    }
}