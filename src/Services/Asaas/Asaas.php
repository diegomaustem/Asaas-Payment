<?php 
namespace App\Services\Asaas;

use Swoole\Coroutine\Http\Client;
use Throwable;
class Asaas
{
    private string $url;
    private string $metodo;
    private string $endPoint;
    private array  $headers = [];
    private string $body;

    public function __construct(string $metodo, string $endPoint, string $body)
    {
        $this->setMetodo($metodo);
        $this->setEndPoint($endPoint);
        $this->setBody($body);
        $this->setUrl($_ENV['API_URL']);

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'access_token' => $_ENV['ASAAS_KEY']
        ];
    }

    public function requisicaoAPIAsaas()
    {
        $cliente = null;
        try {
            $cliente = new Client($this->getUrl(), 443, true);
            $cliente->set(['timeout' => 5]);
            $cliente->setHeaders($this->headers);

            switch($this->getMetodo()) {
                case 'GET':
                    $requisicao= $cliente->get($this->getEndPoint());
                    break;
                case 'POST':
                    $requisicao = $cliente->post($this->getEndPoint(), $this->getBody());
                    break;
                default:
                    return ['status' => 400, 'error' => 'Método não suportado.'];
            }

            if($requisicao === true && $cliente->statusCode == 200) {
                $response = [
                    'status' => $cliente->statusCode,
                    'body' => $cliente->body,
                ];
            } else {
                return ['status' => 500, 'error' => 'Falha em requisição. Tente mais tarde.'];
            }
        } catch (Throwable $th) {
            error_log("Log error: " . $th->getMessage());
            return ['status' => 500, 'error' => 'Falha no servidor. Tente mais tarde.'];
        } finally {
            $cliente->close();
        }
        return $response;
    }

    public function getMetodo(): string
    {
        return $this->metodo;
    }

    public function setMetodo(string $metodo): void
    {
        $this->metodo = $metodo;
    }

    public function getEndPoint(): string
    {
        return $this->endPoint;
    }

    public function setEndPoint(string $endPoint): void 
    {
        $this->endPoint = $endPoint;
    }

    public function getBody(): string 
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getUrl(): string 
    {
        return $this->url;
    }

    public function setUrl(string $url): void 
    {
        $this->url = $url;
    }
}