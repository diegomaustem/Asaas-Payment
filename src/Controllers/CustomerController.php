<?php 
namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Coroutine\Channel;
use App\Services\Asaas\Asaas;
use Throwable;

class CustomerController 
{
    public function index() 
    {
        // $channelClientes = new Channel(1);

        // go(function () use ($channelClientes) {
        //     try {
        //         $asaas = new Asaas('GET', '/v3/customers', '');
        //         $resposta = $asaas->requisicaoAPIAsaas();

        //         $channelClientes->push($resposta);
        //     } catch(Throwable $th) {
        //         error_log("Log error: " . $th->getMessage());
        //         $channelClientes->push([
        //             'status' => 500,
        //             'error' => 'Falha ao processar a requisição.'
        //         ]);
        //     } finally {
        //         $channelClientes->close();
        //     }
        // });

        // $resultado = $channelClientes->pop();

        // if ($resultado['status'] == 200) {
        //     return [
        //         'status' => $resultado['status'], 
        //         'message' => 'Lista de clientes obtida com sucesso.',
        //         'data' => json_decode($resultado['body'])
        //     ];
        // } else {
        //     return ['status' => $resultado['status'], 'error' => $resultado['error']];             
        // }

        return "Clientes";
    }

    public function store(Request $request, Response $response) 
    {
        $dadosCliente = json_decode($request->rawContent(), true);
        $dadosClinteValido = $this->validaDadosCliente($dadosCliente);

        if($dadosClinteValido !== true) {
            return ['status' => 400, 'error' => $dadosClinteValido];
        }

        $channelCliente = new Channel(1);

        go(function () use ($channelCliente, $dadosCliente) {
            try {
                $asaas = new Asaas('POST', '/v3/customers', json_encode($dadosCliente));
                $resposta = $asaas->requisicaoAPIAsaas();

                $channelCliente->push($resposta);
            }catch(Throwable $th) {
                error_log("Log error: " . $th->getMessage());
                $channelCliente->push([
                    'status' => 500,
                    'error' => 'Falha ao processar a requisição.'
                ]);
            }finally {
                $channelCliente->close();
            }
        });

        $resultado = $channelCliente->pop();

        if ($resultado['status'] == 200) {
            return [
                'status' => 201, 
                'message' => 'Cliente cadastrado com sucesso.',
                'data' => json_decode($resultado['body'])
            ];
        } else {
            return ['status' => $resultado['status'], 'error' => $resultado['error']];             
        }
    }

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

