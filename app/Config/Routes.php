<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');



$routes->post('hasheocontra', 'UsuarioController::HasheoContra');

$routes->get('obtenermultas', 'MultasController::getMultas');
$routes->get('obtenerregmultas', 'RegMultasController::getRegMultas');




$routes->group('token', ['filter' => 'ApiTokenFilter'], function($routes){
    $routes->get('obtenerusuarios', 'UsuarioController::getUsuarios');
    $routes->post('verificarcontrasena', 'UsuarioController::verificarContrasena');
    
});



// |||||||||||||||||||||||||||||||||||||||| MANTENIMINETOS ||||||||||||||||||||||||||||||||||||||||


//-------USUARIOS   
$routes->group('usuario', ['filter' => 'ApiTokenFilter'], function ($routes) {
    //OBTENER PERSONAL
    $routes->add('getUsuario', 'UsuarioController::getUsuario');
    //AGREGAR USUARIO
    $routes->post('addUsuario', 'UsuarioController::addUsuario');
    //OBTENER USUARIO POR ID
    $routes->get('getUsuarioById/(:num)', 'UsuarioController::getUsuarioById/$1');
    // ACTUALIZAR USUARIO
    $routes->post('updateUsuario', 'UsuarioController::updateUsuario');
    });


//-------MULTAS   
$routes->group('multas', ['filter' => 'ApiTokenFilter'], function ($routes) {
    //OBTENER MULTAS
    $routes->add('getMultas', 'MultasController::getMultas');
    //AGREGAR MULTA
    $routes->post('addMulta', 'MultasController::addMulta');
    //OBTENER MULTAS POR ID
    $routes->get('getMultasById/(:num)', 'MultasController::getMultasById/$1');
    // ACTUALIZAR MULTAS
    $routes->post('updateMulta', 'MultasController::updateMulta');
    });



//-------REGISTRO DE MULTAS   
$routes->group('regmultas', ['filter' => 'ApiTokenFilter'], function ($routes) {
    //OBTENER REGISTRO DE MULTAS
    $routes->add('getRegMultas', 'RegMultasController::getRegMultas');
    //AGREGAR REGISTRO DE MULTAS
    $routes->post('addRegMulta', 'RegMultasController::addRegMulta');
    //OBTENER REGISTRO DE MULTAS POR ID
    $routes->get('getRegMultasById/(:num)', 'RegMultasController::getRegMultasById/$1');
    //ACTUALIZAR REGISTRO DE MULTAS
    $routes->post('updateRegMulta', 'RegMultasController::updateRegMulta');
    });