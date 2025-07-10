<?php
namespace App\Routes;

// use App\Router;
use App\Controllers\ClientesController;
use App\Controllers\CobrancasController;

$router->get('/listarClientes', ClientesController::class, 'index');
$router->post('/criarCliente', ClientesController::class, 'store');
$router->get('/listarCobrancas', CobrancasController::class, 'index');
$router->get('/criarCobranca', CobrancasController::class, 'store');
