<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Perlitas;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PerlitasController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getArchivos']]);
    }

    // INDEX sirve para sacar todos los registrol de una tabla de la base de datos
    public function index()
    {
        $perlitas = Perlitas::all()->load('usuarios');
        $data = array(
            'code' => 200,
            'status' => 'success',
            'perlitas' => $perlitas
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo un invitado en concreto
    public function show($idCarrera)
    {
        $perlitas = Perlitas::find($idCarrera);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($perlitas)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'perlitas' => $perlitas
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
                'titulo' => 'required|unique:perlitas',
                'perlitas' => 'required',
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
                $perlitas = new Perlitas();
                $perlitas->titulo = $paramsArray['titulo'];
                $perlitas->perlitas = $paramsArray['perlitas'];
                $perlitas->usuarios_id = $user->sub; // Guarda el usuario actual

                // 7.-GUARDAR EN LA BASE DE DATOS
                $perlitas->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El registro se creó correctamente.',
                    'idUltimo' => $perlitas->id
                );
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos.',
                'params' => $paramsArray,
                'objeto' => $params
            );
        }
        return response()->json($data, $data['code']);
    }

    // Metodo para eliminar un registro del PERFIL
    public function destroy($idPerlita)
    {
        // 1.- conseguir el registro
        $perlitas = Perlitas::find($idPerlita);

        // 2.- borrar el registro
        $perlitas->delete();

        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El registro se ha eliminado correctamente.',
            'perlitas' => $perlitas
        );
        return response()->json($data, $data['code']);
    }

    // Metodo que guarda y devuelve le nombre de la imagen.
    public function uploadArchivos(Request $request)
    {
        // Para evitar utlizar el mismo codigo para la autenticacion se debe utilizar un middleware
        // php artisan make:middleware 
        // es un metodo que se ejecuta antes del controlador es como un filtro.

        // 1.- Recoger la imagen desde angular
        $archivo = $request->file('file0'); //Segun angular

        // Validar que solo lleguen imagenes
        $validate = Validator::make($request->all(), [
            // Archivos que se va a permitir
            'file0' => 'required'
        ]);


        // 2.- Guardar la archivo
        // comprobar si la archivo llega o falla la validacion.
        if (!$archivo || $validate->fails()) {

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir el archivo.'
            );
        } else {

            $pdfName = time() . $archivo->getClientOriginalName(); // saca el nombre del la archivo.
            // crear carpeta users luego conf/filesystems.php
            Storage::disk('public')->put($pdfName, File::get($archivo)); // Guarda la archivo en el disco laravel

            // 3.- Delvolver el resultado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'pdf' => $pdfName,
                // 'file' => $archivo
            );
        }
        return response()->json($data, $data['code']); // devuelve un objeto json.
    }

    // Metodo que elimina una imagen del se servidor
    public function destroyArchivos($fileName)
    {
        // comprobar si un archivo existe
        $isset = Storage::disk('public')->exists($fileName);
        if ($isset) {
            $file = Storage::disk('public')->delete($fileName); // Elimina el archivo encontrado.
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'El archivo se elimino correctamente'
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El archivo no existe'
            );
        }
        return response()->json($data, $data['code']); //Devuelve la imagen encontrada
    }

    public function getArchivos($fileName) // recibe el nombre del archivo imagen por parametro.
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
                'message' => 'El archivo no existe'
            );
            return response()->json($data, $data['code']); //Devuelve la imagen encontrada
        }
    }
}