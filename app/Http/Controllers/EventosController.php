<?php

namespace App\Http\Controllers;

use Exception;
use App\Eventos;
use App\Categoria;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventosController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show', 'datosEventos']]);
    }

    // INDEX sirve para sacar todos los registrol del Los REGALOS de la base de datos
    public function index()
    {
        $eventos = Eventos::all()->load('usuarios', 'categoria', 'invitados');
        $data = array(
            'code' => 200,
            'status' => 'success',
            'evento' => $eventos
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo un REGALO en concreto
    public function show($idEventos)
    {
        $eventos = Eventos::find($idEventos);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($eventos)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'evento' => $eventos
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
                'nombreEvento' => 'required|unique:eventos',
                'fecha_evento' => 'required',
                'hora_evento' => 'required',
                'categoria_id' => 'required',
                'invitados_id' => 'required'
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
                $eventos = new Eventos();
                $eventos->nombreEvento = $paramsArray['nombreEvento'];
                $eventos->fecha_evento = $paramsArray['fecha_evento'];
                $eventos->hora_evento = $paramsArray['hora_evento'];
                $eventos->categoria_id = $paramsArray['categoria_id'];
                $eventos->invitados_id = $paramsArray['invitados_id'];
                $eventos->usuarios_id = $user->sub;
                // 7.-GUARDAR EN LA BASE DE DATOS
                $eventos->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Nuevo evento creado correctamente.',
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
    public function update($idEventos, Request $request)
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
                'nombreEvento' => 'required',
                'fecha_evento' => 'required',
                'hora_evento' => 'required',
                'categoria_id' => 'required',
                'invitados_id' => 'required'
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
                // 4.- actualizar el personal en la base de datos
                try {
                    $registrados = Eventos::where('id', $idEventos)->update($paramsArray);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha actualizado correctamente.',
                        'Evento' => $paramsArray
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ya existe un registro con el nombre de este evento.',
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
    public function destroy($idEventos, Request $request)
    {
        // 1.- conseguir el registro
        $eventos = Eventos::find($idEventos);

        // 2.- borrar el registro
        $eventos->delete();

        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El registro ha sido eliminado',
            'personal' => $eventos
        );

        return response()->json($data, $data['code']);
    }

    // Para la pagina web
    public function datosEventos()
    {
        // echo 'hola mundo';
        // die();
        $categoria = Categoria::all();

        foreach ($categoria as $key => $value) {
            // echo $value->id;
            $tresEventos[] = Eventos::where('fecha_evento', '2020-12-04')->where('categoria_id', $value->id)->first()->load('categoria', 'invitados');
        }

        $data = array(
            'code' => 200,
            'status' => 'success',
            'evento' => $tresEventos
        );
        return response()->json($data, $data['code']);
    }
}