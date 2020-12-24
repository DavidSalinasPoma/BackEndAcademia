<?php

namespace App\Http\Controllers;

use App\User;
use App\Perfil;
use Exception;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //Creamos un metodo de prueba el parmetro recibe la informacion que le llega desde http
    public function pruebas(Request $request)
    {
        return 'Pruebas de User-Controller';
    }

    // Metodos de la aplicación recoger los datos de Angular para el registro de usuarios.
    public function register(Request $request)
    {
        // 1.-Recoger los usuarios por post
        $json = $request->input('json', null); // en caso que no llegara nada recibe  NULL
        // probar que llega los datos
        // var_dump($json);
        // Para que no pida ninguna vista y que corte ahi el programa
        // die();
        // Decodificamos el json que nos llega 
        $params = json_decode($json); // Nos devulve un obejto
        // var_dump($params->nom_usuario); // Devuelve un dato

        $paramsArray = json_decode($json, true); // nos devuelve un array
        // var_dump($paramsArray['remember_token']);
        // die();



        // Validar si esta vacio 
        if (!empty($params) && !empty($paramsArray)) {
            // Limpiar datos de espacios en blanco al principio y el final
            $paramsArray = array_map('trim', $paramsArray);

            // 2.-Validar datos
            $validate = Validator::make($paramsArray, [
                // 4.-Comprobar si el usuario ya existe duplicado
                // 'carnet' => 'required|unique:usuarios1',
                'carnet' => 'required',
                'nombres' => 'required|alpha_spaces',
                'apellidos' => 'required|alpha_spaces',
                'imagen' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                // 'perfil_id' => 'required'
                // 'email' => 'requerid|email|inique:user,'.$userIdentificado->sub
                // 'descripcion' => 'required',
                // 'remember_token' => 'required'
            ]);
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'El usuario no se ha creado',
                    'user' => $paramsArray,
                    'errors' => $validate->errors()
                );
            } else {
                // Si la validacion pasa correctamente

                // 3.-Cifrar la contraseña
                $pwd = hash('sha256', $params->password); // se cifra la contraseña 4 veces

                // Crear el objeto usuario para guardar en la base de datos
                $user = new User();
                $user->carnet = $paramsArray['carnet'];
                $user->nombres = $paramsArray['nombres'];
                $user->apellidos = $paramsArray['apellidos'];
                $user->imagen = $paramsArray['imagen'];
                $user->email = $paramsArray['email'];
                $user->password = $pwd;
                // $user->perfil_id = $paramsArray['perfil_id'];


                try {
                    // Guardar en la base de datos
                    // 5.-Crear el usuario
                    $user->save();
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El usuario se ha creado correctamente',
                        'usuario' => $user
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'Error',
                        'code' => 404,
                        'message' => 'Ya existe un registro con el Nro. de carnet o email.'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos.'
            );
        }

        // Devuelve en json con laravel
        return response()->json($data, $data['code']);
    }



    // Metodo que recibe datos por POST de angular para el login
    public function loginAngular(Request $request)
    {
        $jwtauth = new JwtAuth();

        // 1.- Recibir datos por POST.
        $json = $request->input('json', null);
        $params = json_decode($json); // devuelve en un obejto
        $paramsArray = json_decode($json, true); // Devuelve en un array para hacer validaciones.
        // var_dump($paramsArray);
        // die();
        // 2.- Validar los datos recibidos por POST.
        $validate = Validator::make($paramsArray, [
            // 4.-Comprobar si el usuario ya existe duplicado
            'email' => 'required|email',
            'password' => 'required',
            // 'descripcion' => 'required',
            // 'remember_token' => 'required'
        ]);
        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $singup = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // 3.- Cifrar la PASSWORD.
            $pwd = hash('sha256', $params->password); // para verificar que las contraseña a consultar sean iguales.
            // echo $pwd;
            // die();
            // 4.- Devolver token(codificado) o datos(en un objeto decodificado).
            // Este token sera el que recibiremos con el cliente y pasaremos a cada una de las peticines
            // http que realizemos a ciertos metodos de nuestra api, el API lo recibira y procesara el token
            // comprobara si es correcto. y si lo es me dejara entrar y si no lo es no lo hara.
            $singup = $jwtauth->singup($params->email, $pwd); // Por defecto token codificado.


            if (!empty($params->getToken)) { // si existe y no esta vacio y no es NULL.
                $singup = $jwtauth->singup($params->email, $pwd, true); // Token decodificado en un objeto.
            }
            // $singup = $jwtauth->singuPrueba($params->nom_usuario, $pwd, true);
        }
        return response()->json($singup, 200);
    }

    // Metodo para actualizar los datos del usuario(Trabaja con el metodo checkToken)
    public function updateAngular($idUsuario, Request $request)
    {
        $jwtauth = new JwtAuth();
        // echo 'hola';
        // die();
        // $token que nos llega de la cabezera en un hedder de Angular
        $token = $request->header('token-usuario');
        // echo $token;
        // die();
        // 1.- Comprobar si el Usuario esta identificado.
        $checkToken = $jwtauth->checkToken($token); // True si el token es correcto 
        // echo $checkToken;
        // die();

        // 2.- Recoger los datos por POST.
        $json = $request->input('json', null);
        $paramsArray = json_decode($json, true); // devuelve un array

        if ($checkToken == true && !empty($paramsArray)) {
            // Actualizar Usuario.

            // Sacar el usuario identificado
            $userIdentificado = $jwtauth->checkToken($token, true);
            // var_dump($userIdentificado);
            // die();

            // 3.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($paramsArray, [

                // 4.-Comprobar si el carnet y el email ya existe duplicado
                // 'carnet' => 'required|unique:usuarios',
                'nombres' => 'required|alpha_spaces',
                'apellidos' => 'required|alpha_spaces',
                'imagen' => 'required',
                'email' => 'required|email',
                // 'password' => 'required',
                // 'perfil_id' => 'required'

            ]);
            // // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'Datos incorrectos no se puede actualizar,',
                    'errors' => $validate->errors()
                );
            } else {

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['id']);
                unset($paramsArray['password']);
                // unset($paramsArray['antiguo']);
                unset($paramsArray['created_at']);
                unset($paramsArray['remember_token']);

                // 3.- Cifrar la PASSWORD.
                // $paramsArray['password'] = hash('sha256', $paramsArray['password']); // para verificar que las contraseña a consultar sean iguales.
                try {
                    // 5.- Actualizar los datos en la base de datos.
                    $user_update = User::where('id', $idUsuario)->update($paramsArray);

                    // var_dump($user_update);
                    // die();
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'Succes',
                        'code' => 200,
                        'message' => 'El usuario se ha modificado correctamente',
                        'usuario' => $user_update
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'El carnet ó email ya estan registrados',
                        // 'error' => $e
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'El usuario no se esta identificado correctamente',
            );
        }

        return response()->json($data, $data['code']);
    }

    // Metodo para subir una imagen y sacar el nombre a el disco duro de laravel desde Angular
    public function uploadImagen(Request $request)
    {
        // Para evitar utlizar el mismo codigo para la autenticacion se debe utilizar un middleware
        // php artisan make:middleware 
        // es un metodo que se ejecuta antes del controlador es como un filtro.

        // 1.- Recoger la imagen desde angular
        $imagen = $request->file('file0'); //Segun angular

        // Validar que solo lleguen imagenes
        $validate = Validator::make($request->all(), [
            // Archivos que se va a permitir
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif,JPG'
        ]);

        // 2.- Guardar la imagen
        // comprobar si la imagen llega o falla la validacion.
        if (!$imagen || $validate->fails()) {

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen',
                'imagen' => $imagen
            );
        } else {

            $imageName = time() . $imagen->getClientOriginalName(); // saca el nombre del la imagen.
            // crear carpeta users luego conf/filesystems.php
            Storage::disk('public')->put($imageName, File::get($imagen)); // Guarda la imagen en el disco laravel

            // 3.- Delvolver el resultado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $imageName
            );
        }
        return response()->json($data, $data['code']); // devuelve un objeto json.
    }

    // Metodo para sacar la imagen del backent para Angular
    public function getImagen($fileName) // recibe el nombre del archivo imagen por parametro.
    {
        // comprobar si un archivo existe
        $isset = Storage::disk('public')->exists($fileName);

        if ($isset) {
            $file = Storage::disk('public')->get($fileName); // Guarda el archivo encontrado.
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']); //Devuelve la imagen encontrada
        }
    }


    // Metodo para sacar los detalles de UN usuario en concreto
    public function detailUsuario($idUsuario)
    {
        // Guarda en un objeto el usuario
        $user = User::find($idUsuario); // Saca con el usuario relacionado de la base de datos;

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'usuario' => $user
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
        }
        return response()->json($data, $data['code']);
    }
    // INDEX sirve para sacar todos los registros del usuario  de la base de datos
    public function indexUsuario()
    {
        $user = User::all(); // Saca con el usuario relacionado de la base de datos
        $data = array(
            'code' => 200,
            'status' => 'success',
            'usuario' => $user
        );
        return response()->json($data, $data['code']);
    }

    // Metodo para eliminar un registro del PERFIL
    public function destroyUsuario($idUsuario, Request $request)
    {
        // 1.- conseguir el registro
        $user = User::find($idUsuario);

        // 2.- borrar el registro
        $user->delete();

        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El registro ha sido eliminado',
            'usuario' => $user
        );
        return response()->json($data, $data['code']);
    }

    // Metodo para eliminar un registro del PERFIL
    public function destroyPerfil($idPerfil, Request $request)
    {
        // 1.- conseguir el registro
        $perfil = Perfil::find($idPerfil);

        // 2.- borrar el registro
        $perfil->delete();

        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El registro ha sido eliminado',
            'perfil' => $perfil
        );
        return response()->json($data, $data['code']);
    }

    // Metodo que elimina una imagen del se servidor
    public function destroyImagen($nameImag)
    {
        if ($nameImag != 'no-image.png') {
            // comprobar si un archivo existe
            $isset = Storage::disk('public')->exists($nameImag);
            if ($isset) {
                $file = Storage::disk('public')->delete($nameImag); // Elimina el archivo encontrado.
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'La imagen se elimino correctamente'
                );
            } else {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'La imagen no existe dsp'
                );
            }
            return response()->json($data, $data['code']); //Devuelve la imagen encontrada
        }
    }

    // metodo para cambiar el password del usuario
    public function nuevoPass(Request $request)
    {
        // 1.-Recoger los usuarios por post
        $json = $request->input('json', null); // en caso que no llegara nada recibe  NULL
        // probar que llega los datos
        // var_dump($json);
        // Para que no pida ninguna vista y que corte ahi el programa
        // die();
        // Decodificamos el json que nos llega 
        $params = json_decode($json); // Nos devulve un obejto
        // var_dump($params->nom_usuario); // Devuelve un dato

        $paramsArray = json_decode($json, true); // nos devuelve un array
        // echo $params->antiguo;
        // die();

        // Validar si esta vacio 
        if (!empty($params) && !empty($paramsArray)) {
            // Limpiar datos de espacios en blanco al principio y el final
            $paramsArray = array_map('trim', $paramsArray);

            // 2.-Validar datos
            $validate = Validator::make($paramsArray, [

                'antiguo' => 'required',
                'nuevo' => 'required'

            ]);
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'El usuario no se ha creado',
                    'user' => $paramsArray,
                    'errors' => $validate->errors()
                );
            } else {
                // Si la validacion pasa correctamente

                // Guarda en un objeto el usuario
                $users = User::find($params->id); // Saca con el usuario relacionado de la base de datos;

                // 3.-Cifrar la contraseña
                $passAngular = hash('sha256', $params->antiguo); // se cifra la contraseña 4 veces

                if ($users->password === $passAngular) {
                    // 3.-Cifrar la contraseña
                    $pwd = hash('sha256', $params->nuevo); // se cifra la contraseña 4 veces

                    // Crear el objeto usuario para guardar en la base de datos
                    $user = new User();
                    $user->password = $pwd;
                    // echo $users->id . "<br>";
                    // echo $user->password;
                    // die();


                    try {
                        // Guardar en la base de datos
                        // 5.-Crear el usuario
                        $user_update = User::where('id', $users->id)->update(['password' => $user->password]);
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'La contraseña se cambio correctamente',
                            'usuario' => $user_update
                        );
                    } catch (Exception $e) {
                        $data = array(
                            'status' => 'Error',
                            'code' => 404,
                            'message' => 'hubo un error en el registro'
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'Error',
                        'code' => 404,
                        'message' => 'Contraseña antigua incorrecta'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos.'
            );
        }

        // Devuelve en json con laravel
        return response()->json($data, $data['code']);
    }

    public function habilitarUser(Request $request)
    {
        // 1.-Recoger los usuarios por post
        $json = $request->input('json', null); // en caso que no llegara nada recibe  NULL
        // probar que llega los datos
        // var_dump($json);
        // Para que no pida ninguna vista y que corte ahi el programa
        // die();
        // Decodificamos el json que nos llega 
        $params = json_decode($json); // Nos devulve un obejto
        // var_dump($params->nom_usuario); // Devuelve un dato

        $paramsArray = json_decode($json, true); // nos devuelve un array
        // echo $params->antiguo;
        // die();

        // Validar si esta vacio 
        if (!empty($params) && !empty($paramsArray)) {
            // Limpiar datos de espacios en blanco al principio y el final
            $paramsArray = array_map('trim', $paramsArray);

            // 2.-Validar datos
            $validate = Validator::make($paramsArray, [

                'id' => 'required',
                'estado' => 'required'

            ]);
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'Algo salio mal en la operación',
                    'user' => $paramsArray,
                    'errors' => $validate->errors()
                );
            } else {

                try {
                    // Guardar en la base de datos
                    // 5.-Crear el usuario
                    $user_update = User::where('id', $params->id)->update(['estado' => $params->estado]);
                    if ($params->estado === 1) {
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'El usuario se habilito correctamente.',
                            'usuario' => $user_update
                        );
                    } else {
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'El usuario se deshabilito correctamente.',
                            'usuario' => $user_update
                        );
                    }
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'Error',
                        'code' => 404,
                        'message' => 'hubo un error en la operación realizada.'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos.'
            );
        }

        // Devuelve en json con laravel
        return response()->json($data, $data['code']);
    }
}