<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Registrados extends Model
{

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'registrados';

    // Recibindo Regalos
    public function regalos()
    {
        return $this->belongsTo('App\Regalos', 'regalos_id'); //recibe regalos
    }

    public function usuarios()
    {
        return $this->belongsTo('App\User', 'usuarios_id'); //Recibe usuarios
    }
}