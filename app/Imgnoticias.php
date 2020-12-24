<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Imgnoticias extends Model
{
    // Nuestro modelo para la base de datos

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'img_noticias';

    // relacion de uno a muchos inversa(muchos a uno)
    public function noticias()
    {
        return $this->belongsTo('App\Noticias', 'noticias_id'); // Recibe a noticias
    }
}
