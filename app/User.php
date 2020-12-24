<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // Modelos
    protected $table = 'usuarios';
    // 1.- Un usuario puede sacar todos los post asignados o todo lo que haya creado el
    // es una relacion de UNO a MUCHOS OJO
    public function usuarios()
    {
        // Se indica el modelo
        return $this->hasMany('App\User');
    }

    // INICIO DE MODELOS
    // relacion de uno a muchos inversa(muchos a uno)
    public function perfil()
    {
        return $this->belongsTo('App\Perfil', 'perfil_id'); //Recibe el perfil
    }

    // El usuario se dirige a regalos
    public function regalos()
    {
        return $this->hasMany('App\Regalos'); // se dirige hacia regalos
    }

    public function registrados()
    {
        return $this->hasMany('App\Registrados'); // se dirige hacia registrados
    }

    public function invitados()
    {
        return $this->hasMany('App\Invitados'); // se dirige hacia invitados
    }

    public function categoria()
    {
        return $this->hasMany('App\Categoria'); // se dirige hacia categoria
    }


    public function eventos()
    {
        return $this->hasMany('App\Eventos'); // se dirige hacia eventos
    }

    public function acerca()
    {
        return $this->hasMany('App\Acerca'); // se dirige hacia acerca
    }

    public function promocion()
    {
        return $this->hasMany('App\Promocion'); // se dirige hacia Promocion
    }

    public function carrera()
    {
        return $this->hasMany('App\Carrera'); // se dirige hacia Carrera
    }

    public function noticias()
    {
        return $this->hasMany('App\Noticias'); // se dirige hacia Noticias
    }
    public function reflexion()
    {
        return $this->hasMany('App\Reflexion'); // se dirige hacia Noticias
    }
    public function web()
    {
        return $this->hasMany('App\Web'); // se dirige hacia WEB_reflexion
    }
    // El usuario se dirige a Perlitas
    public function perlitas()
    {
        return $this->hasMany('App\Perlitas'); // se dirige Perlitas
    }
    // El usuario se dirige a Perlitas
    public function videos()
    {
        return $this->hasMany('App\Videos'); // se dirige Videos
    }

    // Lo que se puede actualizar
    protected $fillable = [
        'carnet', 'nombres', 'apellidos', 'imagen', 'email', 'email', 'password', 'estado'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}