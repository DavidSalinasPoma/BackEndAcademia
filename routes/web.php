<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Ejemplo de rutas
Route::get('/api/pruebas', 'UserController@pruebas');

/******Rutas para USUARIOS*****/
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@loginAngular');
Route::post('/api/user/upload', 'UserController@uploadImagen')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::get('/api/user/imagen/{nameImag}', 'UserController@destroyImagen');

// para actualizar PUT (pruebas con postMan en el body)
Route::put('/api/user/update/{idUsuario}', 'UserController@updateAngular');

// Para pedir una imagen del usuario GET.
Route::get('/api/user/avatar/{filename}', 'UserController@getImagen');
Route::get('/api/user/detailUsuario/{id}', 'UserController@detailUsuario');
Route::get('/api/user/indexUsuario', 'UserController@indexUsuario');

// para eliminar el perfil y usuario
Route::delete('/api/user/destroyUsuario/{idUser}', 'UserController@destroyUsuario')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::delete('/api/user/destroyPerfil/{idPerfil}', 'UserController@destroyPerfil')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);

// Ruta para cambiar el password del usuario
Route::post('/api/user/newPass', 'UserController@nuevoPass');
Route::put('/api/user/bajaUser', 'UserController@habilitarUser');

/*************RUTAS PARA PERFIL********/
// Utilizando rutas automaticas 
Route::resource('/api/perfil', 'PerfilController');

/*************RUTAS PARA LOS REGALOS********/
// Utilizando rutas automaticas
Route::resource('/api/regalos', 'RegalosController');

/*************RUTAS PARA LOS REGISTRADOS********/
// Utilizando rutas automaticas
Route::resource('/api/registrados', 'RegistradosController');
// para sacar datos de las conferencias por dia (pruebas con postMan en el body)
Route::get('/api/registrados/dias/{pases}', 'RegistradosController@pasesDia');

/*************RUTAS PARA CATEGORIA********/
// Utilizando rutas automaticas
Route::resource('/api/categoria', 'CategoriaController');

/*************RUTAS PARA INVITADOS********/
// Utilizando rutas automaticas 
Route::resource('/api/invitados', 'InvitadosController');
Route::post('/api/invitados/upload', 'InvitadosController@uploadImagen');
Route::get('/api/invitados/avatar/{filename}', 'InvitadosController@getImagen');
Route::get('/api/invitados/imagen/{filename}', 'InvitadosController@destroyImagen');

/*************RUTAS PARA EVENTOS********/
// Utilizando rutas automaticas 
Route::resource('/api/eventos', 'EventosController');
Route::get('/api/eventos/datos/web', 'EventosController@datosEventos');

/*************RUTAS PARA ACERCA DE********/
// Utilizando rutas automaticas 


// Route::group(['middleware' => ['cors']], function () {
//Rutas a las que se permitirá acceso
Route::resource('/api/acerca', 'AcercaController');
Route::get('/api/acerca/avatar/{filename}', 'AcercaController@getImagen');
Route::post('/api/acerca/upload', 'AcercaController@uploadImagen');
Route::get('/api/acerca/destroyImagen/{filename}', 'AcercaController@destroyImagen');
Route::put('/api/acerca/general/acerca/{idAcerca}', 'AcercaController@modificarGeneral');
Route::put('/api/editar/reflexion/{idReflexion}', 'AcercaController@modificarReflexion');
Route::get('/api/recoger/reflexion', 'AcercaController@indexReflexion');

// Ruta para promoción
Route::resource('/api/promocion', 'PromocionController');
Route::post('/api/promocion/upload', 'PromocionController@uploadImagen');
Route::get('/api/promocion/getImagen/{filename}', 'PromocionController@getImagen');
Route::put('/api/promocion/habilitar/bajaPromo', 'PromocionController@bajaPromo');
Route::get('/api/promocion/imagen/destroyImagen/{filename}', 'PromocionController@destroyImagen');

// Ruta para Carrera
Route::resource('/api/carrera', 'CarreraController');
Route::post('/api/carrera/upload', 'CarreraController@uploadImagen');
Route::get('/api/carrera/getImagen/{filename}', 'CarreraController@getImagen');
Route::put('/api/carrera/habilitar/bajaCarrera', 'CarreraController@bajaCarrera');
Route::get('/api/carrera/imagen/destroyImagen/{filename}', 'CarreraController@destroyImagen');
Route::post('/api/carrera/fotos/multiples/{idUltimo}', 'CarreraController@imagenMultiple');
Route::get('/api/carrera/listar/imagenes', 'CarreraController@listarImagenes');
Route::get('/api/carrera/imagen/eliminarImagenes/{idImagen}', 'CarreraController@eliminarImagenes');
Route::get('/api/carrera/imagenes/porCarrera/{idImagen}', 'CarreraController@porCarrera');

// Ruta para Noticias
Route::resource('/api/noticias', 'NoticiasController');
Route::post('/api/noticias/upload', 'NoticiasController@uploadImagen');
Route::get('/api/noticias/getImagen/{filename}', 'NoticiasController@getImagen');
Route::put('/api/noticias/habilitar/bajaCarrera', 'NoticiasController@bajaCarrera');
Route::get('/api/noticias/imagen/destroyImagen/{filename}', 'NoticiasController@destroyImagen');
Route::post('/api/noticias/fotos/multiples/{idUltimo}', 'NoticiasController@imagenMultiple');
Route::get('/api/noticias/listar/imagenes', 'NoticiasController@listarImagenes');
Route::get('/api/noticias/imagen/eliminarImagenes/{idImagen}', 'NoticiasController@eliminarImagenes');
Route::get('/api/noticias/imagenes/porCarrera/{idImagen}', 'NoticiasController@porCarrera');
Route::post('/api/noticias/buscar', 'NoticiasController@buscarNoticias');
// });


// Ruta para Reflexion
Route::resource('/api/reflexion', 'ReflexionController');
Route::post('/api/reflexion/upload', 'ReflexionController@uploadImagen');
Route::get('/api/reflexion/getImagen/{filename}', 'ReflexionController@getImagen');
Route::put('/api/reflexion/habilitar/bajaCarrera', 'ReflexionController@bajaCarrera');
Route::get('/api/reflexion/imagen/destroyImagen/{filename}', 'ReflexionController@destroyImagen');
Route::post('/api/reflexion/fotos/multiples/{idUltimo}', 'ReflexionController@imagenMultiple');
Route::get('/api/reflexion/listar/imagenes', 'ReflexionController@listarImagenes');
Route::get('/api/reflexion/imagen/eliminarImagenes/{idImagen}', 'ReflexionController@eliminarImagenes');
Route::get('/api/reflexion/imagenes/porCarrera/{idImagen}', 'ReflexionController@porCarrera');

// Rutas para perlitas
Route::resource('/api/perlitas', 'PerlitasController');
Route::post('/api/perlitas/upload', 'PerlitasController@uploadArchivos');
Route::get('/api/perlitas/getArchivos/{filename}', 'PerlitasController@getArchivos');
Route::get('/api/perlitas/archivos/{filename}', 'PerlitasController@destroyArchivos');

// Rutas para videos
Route::resource('/api/videos', 'VideosController');
Route::post('/api/videos/upload', 'VideosController@uploadArchivos');
Route::get('/api/videos/getArchivos/{filename}', 'VideosController@getArchivos');
Route::get('/api/videos/archivos/{filename}', 'VideosController@destroyArchivos');

// Ruta para los mensajes
Route::post('/api/message/correo', 'MessagesController@correo');