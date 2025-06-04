<?php 
namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Services\Asaas\Asaas;
use DateTime;
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
        $dadosCobranca = json_decode($request->rawContent(), true);
        $validacao = $this->validaCobranca($dadosCobranca);

        if($validacao !== true) {
            return ['status' => 422, 'error' => $validacao];
        } else {
            $channelCobranca = new Channel(1);

            go(function () use ($channelCobranca, $dadosCobranca) {
                $metodo = 'POST';
                $endPoint = '/v3/payments';

                $asaas = new Asaas($metodo, $endPoint, json_encode($dadosCobranca));
                $resposta = $asaas->requisicaoAPIAsaas();

                $channelCobranca->push($resposta);
            });

            $resultado = $channelCobranca->pop();

            if ($resultado['status'] == 200) {
                return ['status' => $resultado['status'], 'message' => 'Cobrança criada com sucesso.', 'data' => json_decode($resultado['body'])];
            } else {
                return ['status' => $resultado['status'], 'error' => $resultado['error']];
            }
        }
    }
    
    private function validaCobranca(array $dadosCobranca): array|bool
    {
        $errors = [];

        if (!isset($dadosCobranca['customer']) || !is_string($dadosCobranca['customer']) || trim($dadosCobranca['customer']) === '') {
            $errors['customer'] = 'O campo "customer" é obrigatório e deve ser uma string não vazia.';
        } elseif (strlen($dadosCobranca['customer']) < 3) {
            $errors['customer'] = 'O customer deve ter pelo menos 3 caracteres.';
        }

        if (!isset($dadosCobranca['billingType']) || !is_string($dadosCobranca['billingType']) || trim($dadosCobranca['billingType']) === '') {
            $errors['billingType'] = 'O campo "billingType" é obrigatório e deve ser uma string não vazia.';
        } elseif (strtoupper($dadosCobranca['billingType']) !== 'PIX') {
            $errors['billingType'] = 'O billingType deve ser "PIX".';
        }

        if (!isset($dadosCobranca['value']) || !is_numeric($dadosCobranca['value'])) {
            $errors['value'] = 'O campo "value" é obrigatório e deve ser um número.';
        } elseif ($dadosCobranca['value'] <= 0) {
            $errors['value'] = 'O valor deve ser maior que zero.';
        }

        if (!isset($dadosCobranca['dueDate']) || !is_string($dadosCobranca['dueDate']) || trim($dadosCobranca['dueDate']) === '') {
            $errors['dueDate'] = 'O campo "dueDate" é obrigatório e deve ser uma string não vazia.';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $dadosCobranca['dueDate']);
            if (!$date || $date->format('Y-m-d') !== $dadosCobranca['dueDate']) {
                $errors['dueDate'] = 'O campo "dueDate" deve estar no formato YYYY-MM-DD.';
            } elseif ($date < new DateTime('today')) {
                $errors['dueDate'] = 'A data de vencimento não pode ser anterior ao dia atual.';
            }
        }

        return !empty($errors) ? [
            'code' => 422,
            'error' => 'Dados inválidos.',
            'details' => $errors
        ] : true;
    }
}