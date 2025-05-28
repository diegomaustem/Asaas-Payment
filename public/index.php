<?php 
require_once __DIR__ . '/../vendor/autoload.php';

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

use App\Routes\Routes;

$http = new Server("0.0.0.0", 9501);

$http->set([
    'worker_num'      => 4,
    'enable_coroutine' => true,
    'log_file'        => '/tmp/swoole.log',
]);

$http->on('start', function (Server $server) {
    echo "Servidor Rodando." . PHP_EOL;
});

$routes = new Routes();
$rotas  = $routes->getRoutes();

$http->on('request', function (Request $request, Response $response) use ($rotas){
    $response->header("Content-Type", "application/json; charset=utf-8");

    $uri    = $request->server['request_uri'];
    $metodo = $request->server['request_method'];
    $path   = explode("/api", $uri)[1];

    $chaveRota = $metodo . '_' . $path;

    if(!isset($rotas[$chaveRota])){
        $response->status(404);
        $response->end(json_encode([
            'code' => 404,
            'error' => 'Rota não encontrada ou verbo não reconhecido.',
        ]));
    }else {
        $inforRota = $rotas[$chaveRota];
        $classeControlador = $inforRota['controller'];
        $metodoControlador = $inforRota['method'];

        try {
            $instController = new $classeControlador();
            $resposta = $instController->{$metodoControlador}($request, $response);

            $response->end(json_encode([
                'status' => 'success',
                'data'   => $resposta
            ]));
 
        } catch (\Throwable $th) {
            error_log("Log error: " . $th->getMessage());
            $response->status(500);
            $response->end(json_encode([
                'code' => 500,
                'error' => 'Erro interno: serviço indisponível no momento.',

            ]));
        }
    }
});

$http->start();