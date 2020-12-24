<?php

namespace App\Http\Controllers;

use Exception;
use App\Invitados;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InvitadosController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImagen', 'destroyImagen']]);
    }

    // INDEX sirve para sacar todos los registrol del Los REGALOS de la base de datos
    public function index()
    {
        $invitados = Invitados::all()->load('usuarios');
        $data = array(
            'code' => 200,
            'status' => 'success',
            'invitados' => $invitados
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo un invitado en concreto
    public function show($idInvitados)
    {
        $invitados = Invitados::find($idInvitados);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($invitados)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'invitados' => $invitados
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


        // Validamos si esta vacio
        if (!empty($params) && !empty($paramsArray)) {
            // Limpiar datos de espacios en blanco al principio y el final
            // $paramsArray = array_map('trim', $paramsArray);

            // 2.-VALIDAR DATOS
            $validate = Validator::make($paramsArray, [
                'carnet' => 'required|unique:invitados',
                'nombres' => 'required',
                // 'nombres' => 'required|alpha_spaces',
                'apellidos' => 'required',
                'descripcion' => 'required',
                'url_imagen' => 'required'
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
                $token = $request->header('authorization', null);
                $user = $jwtAuth->checkToken($token, true); // Devuelve el token decodificado en un objeto.

                // Si la validacion pasa correctamente  
                // Crear el objeto usuario para guardar en la base de datos
                $invitados = new Invitados();
                $invitados->carnet = $paramsArray['carnet'];
                $invitados->nombres = $paramsArray['nombres'];
                $invitados->apellidos = $paramsArray['apellidos'];
                $invitados->descripcion = $paramsArray['descripcion'];
                $invitados->url_imagen = $paramsArray['url_imagen'];
                $invitados->usuarios_id = $user->sub;


                // 7.-GUARDAR EN LA BASE DE DATOS
                $invitados->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El invitado se ha creado correctamente.',
                );
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos.'
            );
        }
        return response()->json($data, $data['code']);
    }

    // Metodo para actualizar los datos del PERFIL
    public function update($idInvitados, Request $request)
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
                'carnet' => 'required',
                'nombres' => 'required|alpha_spaces',
                'apellidos' => 'required|alpha_spaces',
                'descripcion' => 'required',
                'url_imagen' => 'required'
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
                // 3.- Quitar lo que no quiero actualizar
                unset($paramsArray['id']);
                unset($paramsArray['created_at']);
                unset($paramsArray['estado']);
                unset($paramsArray['usuarios_id']);
                // 4.- actualizar el personal en la base de datos
                try {
                    $registrados = Invitados::where('id', $idInvitados)->update($paramsArray);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha actualizado correctamente.',
                        'invitados' => $paramsArray
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ya existe un registro  con este numero de carnet.',
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

    // Metodo para eliminar un registro del PERFIL
    public function destroy($idInvitados, Request $request)
    {
        // 1.- conseguir el registro
        $invitados = Invitados::find($idInvitados);

        try {
            // 2.- borrar el registro
            $invitados->delete();

            // 3.- Devolver la respuesta.
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El registro ha sido eliminado',
                'personal' => $invitados
            );
        } catch (Exception $th) {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El invitado no se puede eliminar',
                'message2' => 'Esta siento utilizado en otro registro.'
            );
        }

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
            Storage::disk('invitados')->put($imageName, File::get($imagen)); // Guarda la imagen en el disco laravel

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
        $isset = Storage::disk('invitados')->exists($fileName);

        if ($isset) {
            $file = Storage::disk('invitados')->get($fileName); // Guarda el archivo encontrado.
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

    // Metodo que elimina una imagen del se servidor
    public function destroyImagen($fileName)
    {
        // comprobar si un archivo existe
        $isset = Storage::disk('invitados')->exists($fileName);
        if ($isset) {
            $file = Storage::disk('invitados')->delete($fileName); // Elimina el archivo encontrado.
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
}