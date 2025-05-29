<?php 
namespace App\Services\Asaas;

// use Swoole\Coroutine\Http\Client;

use Swoole\Coroutine\Http\Client;
use Swoole\Coroutine;
use Throwable;

class Asaas
{
    private string $url;
    private string $metodo;
    private string $endPoint;
    private array $headers = [];

    public function __construct(string $metodo, string $url, string $endPoint)
    {
        $this->metodo = $metodo;
        $this->url = $url;
        $this->endPoint = $endPoint;

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'access_token' => $_ENV['ASAAS_KEY']
        ];
    }

    public function requisicaoAPIAsaas()
    {
        try {
            $client = new Client($this->getUrl(), 443, true);
            $client->setHeaders($this->headers);
        } catch (Throwable $th) {
            echo "Erro ao iniciar servidor: " . $th->getMessage() . PHP_EOL;
        }

        switch ($this->getMetodo()) {
            case 'GET':
                $client->get($this->getEndPoint());
                break;
            case 'POST':
                $client->post($this->getEndPoint(), $this->body ?? '');
                break;
            default:
                throw new \InvalidArgumentException("Método HTTP não suportado: {$this->getMetodo()}");
        }

        $response = [
            'status' => $client->statusCode,
            'body' => $client->body,
        ];

        $client->close();
        return $response;
    }


    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getMetodo(): string
    {
        return $this->metodo;
    }

    public function setMetodo(string $metodo): void
    {
        $this->metodo = $metodo;
    }

    // public function getTipoReq(): string
    // {
    //     return $this->tipoReq;
    // }

    // public function setTipoReq(string $tipoReq): void
    // {
    //     $this->tipoReq = $tipoReq;
    // }

    // public function getToken(): string
    // {
    //     return $this->token;
    // }

    // public function setToken(string $token): void 
    // {
    //     $this->token = $token;
    // }

    public function getEndPoint(): string
    {
        return $this->endPoint;
    }

    public function setEndPoint(string $endPoint): void 
    {
        $this->endPoint = $endPoint;
    }
}