<?php 
namespace App\Routes;
class Routes
{
    public static function getRoutes() : array
    {
        return [
            'GET_/listarClientes' => 
                ['controller' => 'App\\Controllers\\ClientesController', 'method' => 'index'],
            'POST_/criarCliente' => 
                ['controller' => 'App\\Controllers\\ClientesController', 'method' => 'store'],
            'GET_/listarCobrancas' => 
                ['controller' => 'App\\Controllers\\CobrancasController', 'method' => 'index'],
            'POST_/criarCobranca' => 
                ['controller' => 'App\\Controllers\\CobrancasController', 'method' => 'store'],
        ];
    }
}