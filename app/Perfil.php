<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    // Nuestro modelo para la base de datos

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'perfil';

    // 2.- Para sacar todos los usuarios q esten relacionados con el perfil
    // es una relacion de UNO a MUCHOS
    public function usuarios()
    {
        return $this->hasMany('App\User'); // va para usuarios
    }
}