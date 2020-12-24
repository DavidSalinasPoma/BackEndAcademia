<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\Registrados;
use Carbon\Carbon;
use App\Eventos;
use Exception;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LengthException;

class RegistradosController extends Controller
{
    // Metodo constructor
    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['index', 'show', 'pasesDia']]);
    }

    // INDEX sirve para sacar todos los registrol del Los REGALOS de la base de datos
    public function index()
    {
        $registrados = Registrados::all()->load('usuarios', 'regalos'); // Devuelve un jason
        $totalpagados = Registrados::where('estado', '=', 1)->count();
        $totalSinPagar = Registrados::where('estado', '=', 0)->count();
        $totalGanancias = Registrados::where('estado', '=', 1)->sum('total_pagado');

        // echo $totalGanacias;
        // die();
        $cantidad = count($registrados);

        // $theArray = json_decode($registrados, true); // Array
        // echo $theArray[0]['estado'];
        // var_dump($theArray);

        $data = array(
            'code' => 200,
            'status' => 'success',
            'registrado' => $registrados,
            'totalPagados' => $totalpagados,
            'cantidad' => $cantidad,
            'totalSinPagar' => $totalSinPagar,
            'totalGanancias' => $totalGanancias
        );
        return response()->json($data, $data['code']);
    }

    // SHOW metodo para mostrar una solo un REGALO en concreto
    public function show($idRegistrados)
    {
        $registrados = Registrados::find($idRegistrados)->load('regalos');

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($registrados)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'registrados' => $registrados
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El estudiante no esta registrado'
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
                'nombres' => 'required|alpha_spaces',
                'apellidos' => 'required|alpha_spaces',
                'email' => 'required|email',
                'pases_articulos' => 'required',
                'regalos_id' => 'required',
                'total_pagado' => 'required|numeric',
                // 'usuarios_id' => 'required',
            ]);

            // 5.- SI LA VALIDACION FUE CORRECTA
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El registro no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {

                // CONSEGUIR EL USUARIO IDENTIFICADO->El que hace el registro.
                // $jwtAuth = new JwtAuth();
                // $token = $request->header('authorization', null);
                // $user = $jwtAuth->checkToken($token, true); // Devuelve el token decodificado en un objeto.

                // Si la validacion pasa correctamente  
                // Crear el objeto usuario para guardar en la base de datos
                $registrados = new Registrados();
                $registrados->nombres = $paramsArray['nombres'];
                $registrados->apellidos = $paramsArray['apellidos'];
                $registrados->email = $paramsArray['email'];
                // $registrados->pases_articulos = json_encode($paramsArray['pases_articulos']);
                // Tiene que ser un objeto para convertir en json
                $registrados->pases_articulos = json_encode((object) $paramsArray['pases_articulos']); // Para guardar en json
                $registrados->regalos_id = $paramsArray['regalos_id'];
                $registrados->estado = $paramsArray['estado'];
                $registrados->total_pagado = $paramsArray['total_pagado'];
                // var_dump($registrados->eventos_registrados);
                // die();


                // CONSEGUIR EL USUARIO IDENTIFICADO->El que hace el registro.
                $jwtAuth = new JwtAuth();
                $token = $request->header('authorization', null);
                $user = $jwtAuth->checkToken($token, true); // Devuelve el token decodificado en un objeto.
                $registrados->usuarios_id = $user->sub;

                // 7.-GUARDAR EN LA BASE DE DATOS
                $registrados->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La reserva o compra se realizo correctamente.',
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
    public function update($idRegistrados, Request $request)
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
                'nombres' => 'required|alpha_spaces',
                'apellidos' => 'required|alpha_spaces',
                'email' => 'required|email',
                'pases_articulos' => 'required',
                'regalos_id' => 'required',
                'total_pagado' => 'required|numeric',
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


                // CONSEGUIR EL USUARIO IDENTIFICADO->El que hace el registro.
                $jwtAuth = new JwtAuth();
                $token = $request->header('authorization', null);
                $user = $jwtAuth->checkToken($token, true); // Devuelve el token decodificado en un objeto.
                $paramsArray['usuarios_id'] = $user->sub;
                // $paramsArray['estado'] = 1;
                // 4.- actualizar el personal en la base de datos
                try {
                    $registrados = Registrados::where('id', $idRegistrados)->update($paramsArray);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha actualizado correctamente.',
                        'registrados' => $paramsArray
                    );
                } catch (Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No se ha actualizado correctamente.',
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
    public function destroy($idRegistrados, Request $request)
    {
        // 1.- conseguir el registro
        $registrados = Registrados::find($idRegistrados);

        // 2.- borrar el registro
        $registrados->delete();

        // 3.- Devolver la respuesta.
        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El registro ha sido eliminado',
            'personal' => $registrados
        );

        return response()->json($data, $data['code']);
    }

    // Logica para devolver los talleres por fecha y categoria
    public function pasesDia($pases)
    {
        // Verifica que la fecha exista 
        $diaEventos =  Eventos::whereDate('fecha_evento', $pases)->get()->load('categoria', 'invitados');

        // Saca todas las categorias de la base de datos
        $categorias = Categoria::query()->select(['id', 'eventoCategoria'])->get();
        $array = array();
        for ($i = 0; $i < count($categorias); $i++) {
            // echo $categorias[$i]->id;
            $array[$i] = array(
                'datosEvento' =>  Eventos::whereDate('fecha_evento', $pases)->where('categoria_id', $categorias[$i]->id)->get()->load('categoria', 'invitados'),
                'nombreCategoria' => $categorias[$i]->eventoCategoria
            );
        }

        // var_dump($array);
        // die();
        // $porCategoria = Eventos::whereDate('fecha_evento', $pases)->where('categoria_id', 5)->get();
        // echo $diaEventos;
        // die();
        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($diaEventos)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'array' => $array
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'En este dia no exite ningun evento'
            );
        }
        return response()->json($data, $data['code']);
    }
}