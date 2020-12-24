<?php

namespace App\Http\Controllers;

use App\User;
use App\Perfil;
use App\Acerca;
use Exception;
use App\Helpers\JwtAuth;
use App\Web;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AcercaController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImagen', 'indexReflexion']]);
    }

    // INDEX sirve para sacar todos los registros del usuario  de la base de datos
    public function index()
    {
        $acerca = Acerca::all(); // Saca con el usuario relacionado de la base de datos
        $data = array(
            'code' => 200,
            'status' => 'success',
            'acerca' => $acerca
        );
        return response()->json($data, $data['code']);
    }

    // INDEX sirve para sacar todos los registros del usuario  de la base de datos
    public function indexReflexion()
    {
        $acerca = Web::all(); // Saca con el usuario relacionado de la base de datos
        $data = array(
            'code' => 200,
            'status' => 'success',
            'web' => $acerca
        );
        return response()->json($data, $data['code']);
    }

    public function modificarGeneral($idAcerca, Request $request)
    {
        // 2.- Recoger los datos por POST.
        $json = $request->input('json', null);
        $paramsArray = json_decode($json, true); // devuelve un array

        if (!empty($paramsArray)) {

            // 3.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($paramsArray, [

                // 4.-Comprobar si el carnet y el email ya existe duplicado
                // 'carnet' => 'required|unique:usuarios',

                // 'mision' => 'required',
                // 'vision' => 'required',
                // 'historia' => 'required',
                // 'imagen_uno' => 'required',
                // 'imagen_dos' => 'required',
                // 'imagen_tres' => 'required',
                // 'titulo_uno' => 'required',
                // 'titulo_dos' => 'required',
                // 'titulo_tres' => 'required',
                // 'usuarios_id' => 'required',

                // 'password' => 'required',
                // 'perfil_id' => 'required'

            ]);
            // // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'No se puede actualizar, todos los campos son requeridos',
                    'errors' => $validate->errors()
                );
            } else {

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['id']);
                unset($paramsArray['mision']);
                unset($paramsArray['vision']);
                unset($paramsArray['historia']);
                unset($paramsArray['objetivo']);
                unset($paramsArray['imagen']);
                unset($paramsArray['img_objetivo']);
                unset($paramsArray['imagen_contenido']);
                unset($paramsArray['img_mision']);
                unset($paramsArray['img_vision']);
                unset($paramsArray['created_at']);
                unset($paramsArray['remember_token']);

                // var_dump($paramsArray);
                // echo $idAcerca;
                // die();
                // 3.- Cifrar la PASSWORD.
                // $paramsArray['password'] = hash('sha256', $paramsArray['password']); // para verificar que las contraseña a consultar sean iguales.
                try {
                    // 5.- Actualizar los datos en la base de datos.
                    $acerca_update = Acerca::where('id', $idAcerca)->update($paramsArray);

                    // var_dump($user_update);
                    // die();
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'Succes',
                        'code' => 200,
                        'message' => 'Los datos se han modificado correctamente',
                        'usuario' => $acerca_update
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Algo salio mal al momento de actualizar',
                        'error' => $e
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'El usuario no se esta identificando correctamente',
            );
        }

        return response()->json($data, $data['code']);
    }


    public function modificarReflexion($idReflexion, Request $request)
    {
        // 2.- Recoger los datos por POST.
        $json = $request->input('json', null);
        $paramsArray = json_decode($json, true); // devuelve un array

        if (!empty($paramsArray)) {

            // 3.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($paramsArray, [
                'biblica' => 'required',
                'reflexion' => 'required',
            ]);
            // // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'No se puede actualizar, todos los campos son requeridos',
                    'errors' => $validate->errors()
                );
            } else {

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['id']);
                unset($paramsArray['created_at']);

                // var_dump($paramsArray);
                // echo $idReflexion;
                // die();
                // 3.- Cifrar la PASSWORD.
                // $paramsArray['password'] = hash('sha256', $paramsArray['password']); // para verificar que las contraseña a consultar sean iguales.
                try {
                    // 5.- Actualizar los datos en la base de datos.
                    $web_update = Web::where('id', $idReflexion)->update($paramsArray);

                    // var_dump($user_update);
                    // die();
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'Succes',
                        'code' => 200,
                        'message' => 'Los datos se modificaron correctamente',
                        'web' => $web_update
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Algo salio mal al momento de actualizar',
                        'error' => $e
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'El usuario no se esta identificando correctamente',
            );
        }

        return response()->json($data, $data['code']);
    }

    // Metodo para actualizar los datos del usuario(Trabaja con el metodo checkToken)
    public function update($idAcerca, Request $request)
    {
        // $jwtauth = new JwtAuth(); // aqui recibo el token

        // $token = $request->header('token-usuario');

        // $checkToken = $jwtauth->checkToken($token); // True si el token es correcto 

        // 2.- Recoger los datos por POST.
        $json = $request->input('json', null);
        $paramsArray = json_decode($json, true); // devuelve un array

        if (!empty($paramsArray)) {
            // if ($checkToken == true && !empty($paramsArray)) {
            // Actualizar Usuario.

            // Sacar el usuario identificado
            // $userIdentificado = $jwtauth->checkToken($token, true);
            // var_dump($userIdentificado);
            // die();

            // 3.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($paramsArray, [

                // 4.-Comprobar si el carnet y el email ya existe duplicado
                // 'carnet' => 'required|unique:usuarios',

                // 'mision' => 'required',
                // 'vision' => 'required',
                // 'historia' => 'required',
                // 'imagen' => 'required',
                // 'imagen_contenido' => 'required',
                // 'usuarios_id' => 'required',

                // 'password' => 'required',
                // 'perfil_id' => 'required'

            ]);
            // // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'No se puede actualizar, todos los campos son requeridos',
                    'errors' => $validate->errors()
                );
            } else {

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['id']);
                unset($paramsArray['titulo_uno']);
                unset($paramsArray['titulo_dos']);
                unset($paramsArray['titulo_tres']);
                unset($paramsArray['imagen_uno']);
                unset($paramsArray['imagen_dos']);
                unset($paramsArray['imagen_tres']);
                unset($paramsArray['img_defensa']);
                unset($paramsArray['img_agasajo']);
                unset($paramsArray['img_inscribete']);
                unset($paramsArray['img_contacto']);
                unset($paramsArray['sobre_jac']);
                unset($paramsArray['telefonos']);
                unset($paramsArray['direccion']);
                unset($paramsArray['celular']);
                unset($paramsArray['correo']);
                unset($paramsArray['whatsapp']);


                unset($paramsArray['created_at']);
                unset($paramsArray['remember_token']);

                // var_dump($paramsArray);
                // echo $idAcerca;
                // die();
                // 3.- Cifrar la PASSWORD.
                // $paramsArray['password'] = hash('sha256', $paramsArray['password']); // para verificar que las contraseña a consultar sean iguales.
                try {
                    // 5.- Actualizar los datos en la base de datos.
                    $acerca_update = Acerca::where('id', $idAcerca)->update($paramsArray);

                    // var_dump($user_update);
                    // die();
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'Succes',
                        'code' => 200,
                        'message' => 'Los datos se han modificado correctamente',
                        'usuario' => $acerca_update
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Algo salio mal al momento de actualizar',
                        'error' => $e
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'El usuario no se esta identificando correctamente',
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
                'code' => 200,
                'status' => 'error',
                'message' => 'Error al subir imagen',
                'imagen' => $imagen,
                'fileImagen' => 'vacio'
            );
        } else {

            $imageName = time() . $imagen->getClientOriginalName(); // saca el nombre del la imagen.
            // crear carpeta users luego conf/filesystems.php
            Storage::disk('public')->put($imageName, File::get($imagen)); // Guarda la imagen en el disco laravel

            // 3.- Delvolver el resultado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'imagen' => $imagen,
                'fileImagen' => $imageName,
            );
        }
        return response()->json($data, $data['code']); // devuelve un objeto json.
    }

    // Metodo para sacar la imagen de acerca del backent para Angular
    public function getImagen($fileName) // recibe el nombre del archivo imagen por parametro.
    {
        // comprobar si un archivo existe
        $isset = Storage::disk('public')->exists($fileName);

        if ($isset) {
            $file = Storage::disk('public')->get($fileName); // Guarda el archivo encontrado.
            $url = Storage::url($fileName);
            $urlCompleto = asset($url);

            // Respondiendo con la imagen encontrada
            // return new Response($url, 200);
            $data = array(
                'code' => 200,
                'status' => 'error',
                'message' => 'La imagen no existe',
                'url' => $url,
                'urlCompleto' => $urlCompleto,
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
        }
        return response()->json($data, $data['code']); //Devuelve la imagen encontrada
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
                    'message' => 'La imagen no existe en el storge Acerca'
                );
            }
            return response()->json($data, $data['code']); //Devuelve la imagen encontrada
        }
    }
}