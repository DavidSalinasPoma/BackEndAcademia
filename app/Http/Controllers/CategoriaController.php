<?php

namespace App\Http\Controllers;

use App\Categoria;
use Exception;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    // INDEX sirve para sacar todos los registrol del Los REGALOS de la base de datos
    public function index()
    {
        $categoria = Categoria::all()->load('usuarios');
        $data = array(
            'code' => 200,
            'status' => 'success',
            'categoria' => $categoria
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo una Sola categoria en concreto
    public function show($idCategoria)
    {
        $categoria = Categoria::find($idCategoria);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($categoria)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'categoria' => $categoria
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El perfil no existe'
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
                'eventoCategoria' => 'required|unique:categoria',
                'icono' => 'required'
                // 'usuarios_id' => 'required',
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
                $categoria = new Categoria();
                $categoria->eventoCategoria = $paramsArray['eventoCategoria'];
                $categoria->icono = $paramsArray['icono'];
                $categoria->usuarios_id = $user->sub;
                // 7.-GUARDAR EN LA BASE DE DATOS
                $categoria->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Nueva categoria creada correctamente',
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
    public function update($idCategoria, Request $request)
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
                'eventoCategoria' => 'required',
                'icono' => 'required'
                // 'usuarios_id' => 'required',
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
                unset($paramsArray['usuarios_id']);
                // 4.- actualizar el personal en la base de datos
                try {
                    $registrados = Categoria::where('id', $idCategoria)->update($paramsArray);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha actualizado correctamente.',
                        'perfil' => $paramsArray
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ya existe un registro con este nombre de categoria',
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
    public function destroy($idCategoria, Request $request)
    {
        // 1.- conseguir el registro
        $categoria = Categoria::find($idCategoria);

        // 2.- borrar el registro
        $categoria->delete();

        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El registro ha sido eliminado',
            'personal' => $categoria
        );

        return response()->json($data, $data['code']);
    }
}