<?php

namespace App\Http\Controllers;

use App\perfil;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    // INDEX sirve para sacar todos los registrol del perfil  de la base de datos
    public function index()
    {
        $perfil = Perfil::all();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'perfil' => $perfil
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo un PERFIL en concreto
    public function show($idPerfil)
    {
        $perfil = Perfil::find($idPerfil);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($perfil)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'perfil' => $perfil
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
        // var_dump($paramsArray);
        // die();

        // Validamos si esta vacio
        if (!empty($params) && !empty($paramsArray)) {
            // Limpiar datos de espacios en blanco al principio y el final
            // $paramsArray = array_map('trim', $paramsArray);

            // 2.-VALIDAR DATOS
            $validate = Validator::make($paramsArray, [
                // 4.-COMPROBAR SI EL PERSONAL CON EL NUMERO DE CI YA EXISTE(tabla personal)
                'nombre' => 'required',
                'permisos' => 'required'
            ]);

            // 5.- SI LA VALIDACION FUE CORRECTA
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Ingrese un perfil y seleccione al menos un permiso.',
                    'errors' => $validate->errors()
                );
            } else {
                // Si la validacion pasa correctamente  
                // Crear el objeto usuario para guardar en la base de datos
                $perfil = new Perfil();
                $perfil->nombre = $paramsArray['nombre'];
                // Tiene que ser un objeto para convertir en json
                $perfil->permisos = json_encode((object) $paramsArray['permisos']); // Para guardar en json

                // 7.-GUARDAR EN LA BASE DE DATOS
                try {
                    $perfil->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El nuevo perfil se a creado correctamente.',
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ya existe un registro con este nombre de perfil.',
                        // 'error' => $e
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
        return response()->json($data, $data['code']);
    }

    // Metodo para actualizar los datos del PERFIL
    public function update($idPerfil, Request $request)
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
                // 4.-COMPROBAR SI EL PERSONAL CON EL NUMERO DE CI YA EXISTE(tabla personal)
                'nombre' => 'required',
                'permisos' => 'required'
            ]);
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 404,
                    'message' => 'Ingrese un perfil y seleccione al menos un permiso.',
                    'errors' => $validate->errors()
                );
            } else {
                // 3.- Quitar lo que no quiero actualizar
                unset($paramsArray['id']);
                unset($paramsArray['created_at']);

                // 4.- actualizar el personal en la base de datos
                $paramsArray['permisos'] = json_encode((object) $paramsArray['permisos']); // Para guardar en json
                try {
                    $perfil = Perfil::where('id', $idPerfil)->update($paramsArray);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El perfil se a actualizado correctamente.',
                        'perfil' => $paramsArray
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ya existe un registro con este nombre de perfil.',
                        // 'error' => $e
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se ha enviado ningun perfil para actualizar'
            );
        }
        // 5.- Devolver la respuesta
        return response()->json($data, $data['code']);
    }

    // Metodo para eliminar un registro del PERFIL
    public function destroy($idPerfil, Request $request)
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
            'personal' => $perfil
        );

        return response()->json($data, $data['code']);
    }
}