<?php
// Se define el nombre del paquete donde esta esta clase
namespace App\Helpers;

// Utilizar todos los metodos que tiene esta libreria

use App\Perfil;
use Firebase\JWT\JWT;

// Utilizar la libreria de la base de datos de laravel
// Para realizar consultas a la base de datos sencilla
use Illuminate\Support\Facades\DB;

// Trabajamos con el modelo y utilizar el ORM para hacer a partir de ahi las consultas a la base de datos.
use App\User;


class JwtAuth
{
    // Pasos para crear un token
    // 1.- Buscar si existe el usuario con sus credenciales(name_user y contraseña) en la base de datos
    // 2.- Comprobar si son correctas si el(name_user y contraseña son correctas).
    // 3.- Generar el token con los datos del usuario identificado.(si el nome_usuer y contraseña) son correctas.
    // 4.- Devolver los datos decodificados o el token, en funcion de un parametro.

    // Parametros o propiedades de la clase
    public $key;

    // Metodo Constructor
    public function __construct()
    {
        $this->key = 'Esta es una clave super secreta 123'; // es una clave randon
    }
    // geters and seters(encapsulamiento)
    public function setKey($key)
    {
        $this->key = $key;
    }
    public function getKey()
    {
        return $this->key;
    }

    // Metodos de comportamiento(Primero crear un provider para utilizar en laravel)
    public function singup($email, $password, $getToken = null)
    {
        // 1.- Buscar si existe el usuario con sus credenciales en la base de datos
        $user = User::where([ // guarda en un objeto
            // Comprobar si existe un asuario y password con el nom_usuario q se le esta pasando
            'email' => $email,
            'password' => $password
            // luego sacar datos de la consulta con first()
        ])->first();

        // 2.- Comprobar si son correctas.
        $singup = false;
        if (is_object($user)) {
            $singup = true;
        }
        // 3.- Generar el token con los datos del usuario identificado.
        if ($singup) {
            // $perfil = Perfil::find($user->perfil_id);
            // var_dump($user);
            // die();
            $token = array(
                'sub' => $user->id,
                'carnet' => $user->carnet,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'imagen' => $user->imagen,
                'email' => $user->email,
                // 'password' => $user->password,
                'estado' => $user->estado,
                'usuarios_id' => $user->usuarios_id,
                'created_at' => $user->created_at,
                // 'descripcion' => $user->descripcion,
                // Fecha que se creo el token
                'iat' => time(),
                // fecha que caduca el toque(una semana)
                'exp' => time() + (7 * 24 * 60 * 60)
            );
            // Se utilizara la libreria JWT para generar el token
            // la key es la clave del backend
            // El algoritmo de codificacion HS256
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            // 4.- Devolver los datos decodificados o el token, en funcion de un parametro.
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decode; //Muestra los datos decodificados si recive TRUE
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }

        return $data;
        // Utilizamos el metodo singup en Usercontroller
        // ojo.- Creamos nuestr servisProvider para utilizar el helpers
    }

    // Metodo para saber si el token es correcto y devolver los datos de usuario decodificado en(un objeto).

    public function checkToken($jwt, $getIdentity = false)
    {
        /**VALIDAR SI EL TOKEN ES CORRECTO O INCORRECTO */
        $auth = false; // La utenticacion siempre para estar en falso por defecto
        // Esto es suceptible a errores
        try {
            $jwt = str_replace('"', '', $jwt); //Reemplazar comillas
            $decode = JWT::decode($jwt, $this->key, ['HS256']); //Para decodificar el token
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        // Si decode no esta vacio y es un objeto y si existe el ID del usuario en ese token
        if (!empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }
        /****FIN VALIDAR SI EL TOKEN ES CORRECTO O INCORRECTO */

        // ojoes la clave
        if ($getIdentity == true) { // si esto es verdad devolver el Token decodificado. en un (objeto).
            return $decode;
        }

        return $auth;
    }
}