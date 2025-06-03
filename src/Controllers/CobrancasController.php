<?php 
namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Services\Asaas\Asaas;
use Swoole\Coroutine\Channel;
class CobrancasController
{
    public function index() 
    {
        $channelCobranca = new Channel(1);

        go(function () use ($channelCobranca) {
            $metodo = 'GET';
            $endPoint = '/v3/payments';

            $asaas = new Asaas($metodo, $endPoint, '');
            $resposta = $asaas->requisicaoAPIAsaas();

            $channelCobranca->push($resposta);
        });

        $resultado = $channelCobranca->pop();

        if ($resultado['status'] == 200) {
            return ['status' => $resultado['status'], 'data' => json_decode($resultado['body'])];
        } else {
            return ['status' => $resultado['status'], 'error' => $resultado['error']];
        }
    }

    public function store(Request $request, Response $response) 
    {
        // $dadosEntrada = json_decode($request->rawContent(), true);
        // $validacao = $this->validaEntradas($dadosEntrada);
    }
    
    private function validaEntradas(array $dadosEntrada): array|bool
    {
        $errors = [];

        if (!isset($dadosEntrada['nome']) || !is_string($dadosEntrada['nome']) || trim($dadosEntrada['nome']) === '') {
            $errors['nome'] = 'O campo "nome" é obrigatório e deve ser uma string não vazia.';
        } elseif (strlen($dadosEntrada['nome']) < 3) {
            $errors['nome'] = 'O nome deve ter pelo menos 3 caracteres.';
        }

        if (!isset($dadosEntrada['idade']) || !is_int($dadosEntrada['idade'])) {
            $errors['idade'] = 'O campo "idade" é obrigatório e deve ser um número inteiro.';
        } elseif ($dadosEntrada['idade'] <= 0 || $dadosEntrada['idade'] > 150) {
            $errors['idade'] = 'A idade deve ser um número entre 1 e 150.';
        }

        return !empty($errors) ? [
            'code' => 422,
            'error' => 'Dados inválidos.',
            'details' => $errors
        ] : true;
    }
}