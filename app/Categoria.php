<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    // Nuestro modelo para la base de datos

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'categoria';

    // 2.- Para sacar todos los usuarios q esten relacionados con el perfil
    // es una relacion de UNO a MUCHOS
    public function eventos()
    {
        return $this->hasMany('App\Eventos'); // se dirige hacia eventos
    }

    // relacion de uno a muchos inversa(muchos a uno)
    public function usuarios()
    {
        return $this->belongsTo('App\User', 'usuarios_id'); // Recibe a usuario
    }
}