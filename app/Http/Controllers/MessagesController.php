<?php

namespace App\Http\Controllers;

use App\Mail\FormularioContacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller
{

    public function __construct()
    {
        // Utiliza la autenticacion en toda la clase excepto en los metodos de index y show.
        $this->middleware('api.auth', ['except' => ['correo']]);
    }

    // STORE Permite guardar los datos de en la base de datos
    // Metodos de comportamiento Con este parametro recibimos todo de Angular
    public function correo(Request $request)
    {
        // 1.- RECIBIR DATOS
        // Recibimos los datos de angular en una variable
        $json = $request->input('json', null);
        // Convertimos los datos en objeto y array
        $params = json_decode($json); // objeto
        $paramsArray = json_decode($json, true); // Array
        // $thearray = get_object_vars($params); // de objeto a array


        // var_dump($paramsArray);
        // die();
        // Validamos si esta vacio
        if (!empty($params) && !empty($paramsArray)) {

            // 2.-VALIDAR DATOS
            $validate = Validator::make($paramsArray, [
                'nombres' => 'required',
                // 'descripcion' => 'required',
                'celular' => 'required',
                'correo' => 'required',

            ]);

            // 5.- SI LA VALIDACION FUE CORRECTA
            // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El correo no se pudo enviar.',
                    'errors' => $validate->errors()
                );
            } else {
                Mail::to('ventas@jacbolivia2000.com')->queue(new FormularioContacto($paramsArray));

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El correo se envio correctamente',
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
}