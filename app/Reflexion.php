<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reflexion extends Model
{
    // Nuestro modelo para la base de datos

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'reflexion';

    // relacion de uno a muchos inversa(muchos a uno)
    public function usuarios()
    {
        return $this->belongsTo('App\User', 'usuarios_id'); // Recibe a usuario
    }
}