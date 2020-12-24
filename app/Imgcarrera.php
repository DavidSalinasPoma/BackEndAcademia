<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Imgcarrera extends Model
{
    // Nuestro modelo para la base de datos

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'img_carrera';

    // relacion de uno a muchos inversa(muchos a uno)
    public function carrera()
    {
        return $this->belongsTo('App\Carrera', 'carrera_id'); // Recibe a carrera
    }
}