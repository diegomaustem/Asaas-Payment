<?php 
namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Coroutine\Http\Client;
use App\Services\Asaas\Asaas;
use DateTime;
use Swoole\Coroutine\Channel;
use Throwable;

class DebtController
{
    public function index() 
    {
        $channelDebts = new Channel(1);
        
        go(function () use ($channelDebts) {
            try {
                $assasService = new Asaas(new Client($_ENV['API_URL'], PORT, SSL)); 
                $debts = $assasService->fetchDebts();

                $channelDebts->push($debts);
            } catch(Throwable $th) {
                error_log("Log errorr: " . $th->getMessage());
                throw $th;
            } finally {
                $channelDebts->close();
            }
        });
        $allDebts = $channelDebts->pop();
        return json_decode($allDebts);
    }

    public function store(Request $request, Response $response) 
    {
        $dadosCobranca = json_decode($request->rawContent(), true);
        $validacao = $this->validaCobranca($dadosCobranca);

        if($validacao !== true) {
            return ['status' => 422, 'error' => $validacao];
        } 

        $channelCobranca = new Channel(1);

        go(function () use ($channelCobranca, $dadosCobranca) {
            try {
                $asaas = new Asaas('POST', '/v3/payments', json_encode($dadosCobranca));
                $resposta = $asaas->requisicaoAPIAsaas();

                $channelCobranca->push($resposta);
            }catch(Throwable $th){
                error_log("Log error: " . $th->getMessage());
                $channelCobranca->push([
                    'status' => 500,
                    'error' => 'Falha ao processar a requisição.'
                ]);
            }finally {
                $channelCobranca->close();
            }
        });

        $resultado = $channelCobranca->pop();

        if ($resultado['status'] == 200) {
                return ['status' => 201, 'message' => 'Cobrança criada com sucesso.', 'data' => json_decode($resultado['body'])];
        } else {
                return ['status' => $resultado['status'], 'error' => $resultado['error']];
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