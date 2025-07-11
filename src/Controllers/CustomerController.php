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

    public function store(Request $request, Response $response) 
    {
        $customerData = json_decode($request->rawContent(), true);
        $validCustomer = $this->validateCustomerData($customerData);

                // var_dump($validCustomer);die();

        if($validCustomer !== true) {
            $response->status(422);
            $response->end(json_encode([
                'code' => 422, 
                'error' => 'Validation error in customer data.', 
                'details' => $validCustomer
            ]));
            return;
        }

        $channelCustomer = new Channel(1);

        go(function () use ($channelCustomer, $customerData) {
            try {
                $assasService = new Asaas(new Client($_ENV['API_URL'], PORT, SSL)); 
                $custumer = $assasService->createCustomer(json_encode($customerData));

                $channelCustomer->push($custumer);
            }catch(Throwable $th) {
                error_log("Log errorr: " . $th->getMessage());
                throw $th;
            }finally {
                $channelCustomer->close();
            }
        });

        $customer = $channelCustomer->pop();
        return json_decode($customer);
    }

    private function validateCustomerData(array $customerData): array|bool
    {
        $errors = [];

        if (!isset($customerData['name']) || !is_string($customerData['name']) || trim($customerData['name']) === '') {
            $errors['name'] = 'The name field is required and must be a non-empty string.';
        } elseif (strlen($customerData['name']) < 3) {
            $errors['name'] = 'The name must be at least 3 characters long.';
        }

        if (!isset($customerData['cpfCnpj']) || !is_string($customerData['cpfCnpj']) || trim($customerData['cpfCnpj']) === '') {
            $errors['cpfCnpj'] = 'The cpfCnpj field is mandatory and must be a non-empty string.';
        } elseif (!preg_match('/^\d{11}$|^\d{14}$/', $customerData['cpfCnpj'])) {
            $errors['cpfCnpj'] = 'The CPF/CNPJ must contain 11 or 14 digits.';
        }

        if (!isset($customerData['mobilePhone']) || !is_string($customerData['mobilePhone']) || trim($customerData['mobilePhone']) === '') {
            $errors['mobilePhone'] = 'The cellphone field is required and must be a non-empty string.';
        } elseif (!preg_match('/^\d{10,11}$/', $customerData['mobilePhone'])) {
            $errors['mobilePhone'] = 'The mobile phone number must contain between 10 and 11 digits.';
        }

        return empty($errors) ? true : $errors;
    }
}

