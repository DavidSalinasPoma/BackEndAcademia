<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Eventos extends Model
{
    // Nuestro modelo para la base de datos

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'eventos';

    // relacion de uno a muchos inversa(muchos a uno)
    public function categoria()
    {
        return $this->belongsTo('App\Categoria', 'categoria_id'); // Recibe a categoria
    }

    // relacion de uno a muchos inversa(muchos a uno)
    public function invitados()
    {
        return $this->belongsTo('App\Invitados', 'invitados_id'); // Recibe a invitados
    }

    // relacion de uno a muchos inversa(muchos a uno)
    public function usuarios()
    {
        return $this->belongsTo('App\User', 'usuarios_id'); // Recibe a usuario
    }
}