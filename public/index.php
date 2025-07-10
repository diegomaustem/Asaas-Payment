<?php 
require_once __DIR__ . '/../vendor/autoload.php';

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Router\Router;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$http = new Server("0.0.0.0", 9501);

$http->set([
    'worker_num'      => 4,
    'enable_coroutine' => true,
    'log_file'        => '/tmp/swoole.log',
]);

$http->on('start', function (Server $server) {
    echo "Servidor Rodando." . PHP_EOL;
});

$router = new Router("/api");

(function (Router $router) {
    require __DIR__ . '/../src/Routes/api.php'; 
})($router);

$http->on('request', function (Request $request, Response $response) use ($router){
    $router->dispatch($request, $response);
});

$http->start();