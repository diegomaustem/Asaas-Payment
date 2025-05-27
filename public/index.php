<?php 
require_once __DIR__ . '/../vendor/autoload.php';

use App\Routes\Routes;
use App\Utils\Mensagens;

$verbo = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'] ?? '';

$metodo = explode("/api", $uri)[1];

$routes = new Routes();
$rotas = $routes->getRoutes();

foreach ($rotas['rotas'] as $rota) {
    if($rota['path'] === $metodo && $rota['verb'] === $verbo){

        $controller = $rota['controller'];

        try {
            $instaciaController = new $controller($rota['method']);
            $instaciaController->{$rota['method']}(); 
        }catch(\Throwable $th){
            error_log("Log error:" . $th->getMessage());
            Mensagens::erro("Erro interno: serviço indisponível no momento.", 500);
        }
    }else{
        Mensagens::erro('Rota não encontrada ou verbo não reconhecido.', 404);
    }
}