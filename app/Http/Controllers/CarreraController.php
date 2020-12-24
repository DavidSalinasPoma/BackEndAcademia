<?php

namespace App\Http\Controllers;

use App\Promocion;
use App\Carrera;
use App\Imgcarrera;
use Exception;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Input;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;



class CarreraController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImagen', 'bajaCarrera', 'destroyImagen', 'listarImagenes', 'eliminarImagenes', 'porCarrera']]);
    }

    // INDEX sirve para sacar todos los registrol de promociones  de la base de datos
    public function index()
    {
        $carrera = Carrera::all()->load('usuarios');
        $data = array(
            'code' => 200,
            'status' => 'success',
            'carrera' => $carrera
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo un invitado en concreto
    public function show($idCarrera)
    {
        $carrera = Carrera::find($idCarrera);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($carrera)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'carrera' => $carrera
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El invitado no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

    // STORE Permite guardar los datos de en la base de datos
    // Metodos de comportamiento Con este parametro recibimos todo de Angular
    public function store(Request $request)
    {
        // 1.- RECIBIR DATOS
        // Recibimos los datos de angular en una variable
        $json = $request->input('json', null);

        // Convertimos los datos en objeto y array
        $params = json_decode($json); // objeto
        $paramsArray = json_decode($json, true); // Array
        // $thearray = get_object_vars($params); // de objeto a array



        // Validamos si esta vacio
        if (!empty($params) && !empty($paramsArray)) {

            // 2.-VALIDAR DATOS
            $validate = Validator::make($paramsArray, [
                'titulo' => 'required',
                // 'descripcion' => 'required',
                'imagen' => 'required',
                'tipo' => 'required',
                'tituloWeb' => 'required'
            ]);

            // 5.- SI LA VALIDACION FUE CORRECTA
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El registro no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {

                // CONSEGUIR EL USUARIO IDENTIFICADO->El que hace el registro.
                $jwtAuth = new JwtAuth();
                $token = $request->header('token-usuario', null);
                $user = $jwtAuth->checkToken($token, true); // Devuelve el token decodificado en un objeto.

                // Si la validacion pasa correctamente  
                // Crear el objeto usuario para guardar en la base de datos
                $carrera = new Carrera();
                $carrera->titulo = $paramsArray['titulo'];
                $carrera->descripcion = $paramsArray['descripcion'];
                // htmlentities($paramsArray['descripcion']);
                $carrera->imagen = $paramsArray['imagen'];
                $carrera->tipo = $paramsArray['tipo'];
                $carrera->tituloWeb = $paramsArray['tituloWeb'];
                $carrera->usuarios_id = $user->sub;

                // 7.-GUARDAR EN LA BASE DE DATOS
                $carrera->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La carrera se  creado correctamente.',
                    'idUltimo' => $carrera->id
                );
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos. David',
                'params' => $paramsArray,
                'objeto' => $params
            );
        }
        return response()->json($data, $data['code']);
    }


    // Metodo para actualizar los datos del PERFIL
    public function update(Request $request)
    {
        // la utenticacion se hara de forma automatica
        // 1.- Recoger los datos por post.
        $json = $request->input('json', null);
        $paramsArray = json_decode($json, true); // convierte un json en array

        // Validamos lo que nos llega que no este vacio
        if (!empty($paramsArray)) {
            // 2.- Validar los datos.
            // 2.-VALIDAR DATOS
            $validate = Validator::make($paramsArray, [
                'titulo' => 'required',
                'descripcion' => 'required',
                'imagen' => 'required',
                'tipo' => 'required',
                'tituloWeb' => 'required'

            ]);
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 404,
                    'message' => 'Ingresó de datos incorrectos.',
                    'errors' => $validate->errors()
                );
            } else {
                $idCarrera = $paramsArray['id']; // id de la carrera a actualizar
                // 3.- Quitar lo que no quiero actualizar
                unset($paramsArray['id']);
                unset($paramsArray['created_at']);
                // unset($paramsArray['estado']);
                unset($paramsArray['usuarios_id']);
                unset($paramsArray['imgMultiple']);
                // 4.- actualizar el personal en la base de datos
                try {
                    $registrados = Carrera::where('id', $idCarrera)->update($paramsArray);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha actualizado correctamente.',
                        'carrera' => $paramsArray
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No se pudo actualizar',
                        'invitdos' => $paramsArray
                        // 'error' => $e
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No hay datos para actualizar'
            );
        }
        // 5.- Devolver la respuesta
        return response()->json($data, $data['code']);
    }

    // Metodo que guarda y devuelve le nombre de la imagen.
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
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);


        // 2.- Guardar la imagen
        // comprobar si la imagen llega o falla la validacion.
        if (!$imagen || $validate->fails()) {

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else {

            $imageName = time() . $imagen->getClientOriginalName(); // saca el nombre del la imagen.
            // crear carpeta users luego conf/filesystems.php
            Storage::disk('public')->put($imageName, File::get($imagen)); // Guarda la imagen en el disco laravel

            // 3.- Delvolver el resultado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $imageName,
                'file' => $imagen
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
            return new Response($file, 200); // devuelve el archivo
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']); //Devuelve la imagen encontrada
        }
    }

    public function bajaCarrera(Request $request)
    {
        // 1.-Recoger los usuarios por post
        $json = $request->input('json', null); // en caso que no llegara nada recibe  NULL

        $params = json_decode($json);

        $paramsArray = json_decode($json, true);

        // Validar si esta vacio 
        if (!empty($params) && !empty($paramsArray)) {
            // Limpiar datos de espacios en blanco al principio y el final
            // $paramsArray = array_map('trim', $paramsArray);

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
                    $carrera_update = Carrera::where('id', $params->id)->update(['estado' => $params->estado]);
                    if ($params->estado === 1) {
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'La carrera se habilito correctamente.',
                            'estado' => $params->estado,
                            'carrera' => $carrera_update
                        );
                    } else {
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'La carrera se deshabilito correctamente.',
                            'estado' => $params->estado,
                            'carrera' => $carrera_update
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

    // Metodo que elimina una imagen del se servidor
    public function destroyImagen($fileName)
    {
        // comprobar si un archivo existe
        $isset = Storage::disk('public')->exists($fileName);
        if ($isset) {
            $file = Storage::disk('public')->delete($fileName); // Elimina el archivo encontrado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'La imagen se elimino correctamente'
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

    // Metodo que guarda la multimples imagenes 
    public function imagenMultiple($idCarrera, Request $request)
    {
        // 1.- Recoger la imagen desde angular
        $imagen = $request->file('file0'); //Segun angular

        $imageName = time() . $imagen[0]->getClientOriginalName(); // saca el nombre del la imagen.
        // // crear carpeta users luego conf/filesystems.php
        Storage::disk('public')->put($imageName, File::get($imagen[0])); // Guarda la imagen en el disco laravel


        $imagenCarrera = new Imgcarrera();
        $imagenCarrera->carrera_id = $idCarrera;
        $imagenCarrera->imagen = $imageName;

        // Guardando la base de datos
        $imagenCarrera->save();

        $data = array(
            'code' => 200,
            'status' => 'success',
            'message' => 'La imagen se guardo corectamente',
            'idUltino' => $idCarrera,
            'fotos' => $imagen,
            'NaneImegen' => $imageName
        );

        return response()->json($data, $data['code']); //Devuelve la imagen encontrada
    }

    // Metodo que lista todas lasimagenes
    // INDEX sirve para sacar todos los registrol de promociones  de la base de datos
    public function listarImagenes()
    {
        $imagenes = Imgcarrera::all();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'imagenes' => $imagenes
        );
        return response()->json($data, $data['code']);
    }

    // Eliminar la imagende la base de datos
    public function eliminarImagenes($idImagen, Request $request)
    {
        // 1.- conseguir el registro

        $imagen = Imgcarrera::find($idImagen);
        $nameImagen = $imagen->imagen;

        // 2.- borrar el registro
        $imagen->delete();
        $file = Storage::disk('public')->delete($nameImagen);
        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'La imagen ha sido eliminada',
            'imagen' => $imagen
        );
        return response()->json($data, $data['code']);
    }

    // Lista de imagenes por carrera
    public function porCarrera($idImagen, Request $request)
    {
        $imagenes = Imgcarrera::where('carrera_id', $idImagen)->get();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'imagenes' => $imagenes
        );
        return response()->json($data, $data['code']);
    }
}