<?php 

namespace App\Routes;

class Routes
{
    public static function getRoutes() : array
    {
        return [
            'rotas' => [
                ['path' => '/criarCobranca',
                'controller' => 'App\\Controllers\\CobrancasController',
                'method' => 'criarCobranca',
                'verb' => 'POST'],

                ['path' => '/listarCobrancas',
                'controller' => 'App\\Controllers\\CobrancasController',
                'method' => 'listarCobrancas', 
                'verb' => 'GET']
            ]
        ];
    }
}