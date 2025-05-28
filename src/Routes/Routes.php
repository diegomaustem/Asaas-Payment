<?php 
namespace App\Routes;
class Routes
{
    public static function getRoutes() : array
    {
        return [
            'GET_/listarCobrancas' => 
                ['controller' => 'App\\Controllers\\CobrancasController', 'method' => 'index'],
            'POST_/criarCobranca' => 
                ['controller' => 'App\\Controllers\\CobrancasController', 'method' => 'store'],
        ];
    }
}