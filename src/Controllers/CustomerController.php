<?php 
namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Client;
use App\Services\Asaas\Asaas;
use Throwable;

define('PORT', 443);
define('SSL', TRUE);

class CustomerController 
{
    public function index() 
    {
        $channelCustomers = new Channel(1);

        go(function () use ($channelCustomers) {
            try {
                $assasService = new Asaas(new Client($_ENV['API_URL'], PORT, SSL)); 
                $custumers = $assasService->fetchCustomers();

                $channelCustomers->push($custumers);
            } catch(Throwable $th) {
                error_log("Log errorr: " . $th->getMessage());
                throw $th;
            } finally {
                $channelCustomers->close();
            }
        });

        $allCustomers = $channelCustomers->pop();
        return json_decode($allCustomers);
    }

    // public function store(Request $request, Response $response) 
    // {
    //     $dadosCliente = json_decode($request->rawContent(), true);
    //     $dadosClinteValido = $this->validaDadosCliente($dadosCliente);

    //     if($dadosClinteValido !== true) {
    //         return ['status' => 400, 'error' => $dadosClinteValido];
    //     }

    //     $channelCliente = new Channel(1);

    //     go(function () use ($channelCliente, $dadosCliente) {
    //         try {
    //             $asaas = new Asaas('POST', '/v3/customers', json_encode($dadosCliente));
    //             $resposta = $asaas->requisicaoAPIAsaas();

    //             $channelCliente->push($resposta);
    //         }catch(Throwable $th) {
    //             error_log("Log error: " . $th->getMessage());
    //             $channelCliente->push([
    //                 'status' => 500,
    //                 'error' => 'Falha ao processar a requisição.'
    //             ]);
    //         }finally {
    //             $channelCliente->close();
    //         }
    //     });

    //     $resultado = $channelCliente->pop();

    //     if ($resultado['status'] == 200) {
    //         return [
    //             'status' => 201, 
    //             'message' => 'Cliente cadastrado com sucesso.',
    //             'data' => json_decode($resultado['body'])
    //         ];
    //     } else {
    //         return ['status' => $resultado['status'], 'error' => $resultado['error']];             
    //     }
    // }

    private function validaDadosCliente(array $dadosCliente): array|bool
    {
        $errors = [];

        if (!isset($dadosCliente['name']) || !is_string($dadosCliente['name']) || trim($dadosCliente['name']) === '') {
            $errors['name'] = 'O campo "name" é obrigatório e deve ser uma string não vazia.';
        } elseif (strlen($dadosCliente['name']) < 3) {
            $errors['name'] = 'O nome deve ter pelo menos 3 caracteres.';
        }

        if (!isset($dadosCliente['cpfCnpj']) || !is_string($dadosCliente['cpfCnpj']) || trim($dadosCliente['cpfCnpj']) === '') {
            $errors['cpfCnpj'] = 'O campo "cpfCnpj" é obrigatório e deve ser uma string não vazia.';
        } elseif (!preg_match('/^\d{11}$|^\d{14}$/', $dadosCliente['cpfCnpj'])) {
            $errors['cpfCnpj'] = 'O CPF/CNPJ deve conter 11 ou 14 dígitos.';
        }

        if (!isset($dadosCliente['mobilePhone']) || !is_string($dadosCliente['mobilePhone']) || trim($dadosCliente['mobilePhone']) === '') {
            $errors['mobilePhone'] = 'O campo "mobilePhone" é obrigatório e deve ser uma string não vazia.';
        } elseif (!preg_match('/^\d{10,11}$/', $dadosCliente['mobilePhone'])) {
            $errors['mobilePhone'] = 'O telefone móvel deve conter entre 10 e 11 dígitos.';
        }

        return empty($errors) ? true : $errors;
    }
}

