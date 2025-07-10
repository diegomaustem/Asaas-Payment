<?php
namespace App\Router;

use Swoole\Http\Request;
use Swoole\Http\Response;

class Router
{
    private array $routes = [];
    private string $apiPrefix;

    public function __construct(string $apiPrefix = '/api')
    {
        $this->apiPrefix = $apiPrefix;
    }

    public function get(string $path, string $controller, string $method): void
    {
        
        $this->addRoute('GET', $path, $controller, $method);
    }

    public function post(string $path, string $controller, string $method): void
    { 
        $this->addRoute('POST', $path, $controller, $method);
    }

    private function addRoute(string $httpMethod, string $path, string $controller, string $method): void
    {
        $key = $httpMethod . '_' . $path;
        $this->routes[$key] = [
            'controller' => $controller,
            'method' => $method,
        ];
    }
    
    public function dispatch(Request $request, Response $response): void
    {
        $response->header("Content-Type", "application/json; charset=utf-8");

        $uri = $request->server['request_uri'];
        $method = $request->server['request_method'];

        // Garante que o prefixo da API existe na URI antes de fazer o explode
        if (strpos($uri, $this->apiPrefix) === 0) {
            $path = explode($this->apiPrefix, $uri, 2)[1];
        } else {
            $response->status(404);
            $response->end(json_encode([
                'code' => 404,
                'status' => "error",
                'message' => 'API route not found. Invalid prefix.',
            ]));
            return;
        }

        $routeKey = $method . '_' . $path;

        if (!isset($this->routes[$routeKey])) {
            $response->status(404);
            $response->end(json_encode([
                'code' => 404,
                'status' => "error",
                'message' => 'Route not found or verb not recognized.',
            ]));
            return;
        }

        $routeInfo = $this->routes[$routeKey];
        $controllerClass = $routeInfo['controller'];
        $controllerMethod = $routeInfo['method'];

        try {
            $controllerInstance = new $controllerClass();
            $responseFromController = $controllerInstance->{$controllerMethod}($request, $response);

            $response->status(200);
            $response->end(json_encode(['code' => 200, 'status' => 'success', $responseFromController]));

        } catch (\Throwable $th) {
            error_log("Log error: " . $th->getMessage() . " on " . $th->getFile() . " line " . $th->getLine());
            $response->status(500);
            $response->end(json_encode([
                'code' => 500,
                'status' => "error",
                'message' => 'Internal error: Service currently unavailable.',
            ]));
        }
    }
}