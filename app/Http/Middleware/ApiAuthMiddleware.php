<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\JwtAuth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Este metodo comprueba si el usuario esta identificado
        $jwtauth = new JwtAuth();
        // $token que nos llega de la cabezera en un hedder de Angular
        $token = $request->header('token-usuario');
        // $token = $request->header('APP_KEY');

        // 1.- Comprobar si el Usuario esta identificado.
        $checkToken = $jwtauth->checkToken($token); // True si el token es correcto 
        // echo $checkToken;
        // die();
        if ($checkToken) {
            return $next($request);
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'El usuario no se esta identificado Middleware.'
            );
            return response()->json($data, $data['code']);
        }
        // Luego se configura para que funcione el middleware
    }
}
