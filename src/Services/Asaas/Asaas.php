<?php 
namespace App\Services\Asaas;

use Swoole\Coroutine\Http\Client;
use Throwable;
class Asaas
{
    private Client $client; 

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->client->setHeaders(
            ['Content-Type' => 'application/json', 'Accept' => 'application/json','access_token' => $_ENV['ASAAS_KEY']]
        );
        $this->client->set(['timeout' => 5]);
    }

    public function fetchCustomers()
    {
        try { 
            $this->client->get(URL_CUSTOMERS);
            return $this->client->body;
        } catch (Throwable $th) {
            error_log("Log error: " . $th->getMessage());
            throw $th;
        } finally {
            $this->client->close();
        }
    }

    public function createCustomer($customer) 
    {
        try { 
            $this->client->post(URL_CUSTOMERS, $customer);
            return $this->client->body;
        } catch (Throwable $th) {
            error_log("Log error: " . $th->getMessage());
            throw $th;
        } finally {
            $this->client->close();
        }
    }

    public function fetchDebts()
    {
        try { 
            $this->client->get(URL_PAYMENTS);
            return $this->client->body;
        } catch (Throwable $th) {
            error_log("Log error: " . $th->getMessage());
            throw $th;
        } finally {
            $this->client->close();
        }
    }

    public function createDebt($debt) 
    {
        try { 
            $this->client->post(URL_PAYMENTS, $debt);
            return $this->client->body;
        } catch (Throwable $th) {
            error_log("Log error: " . $th->getMessage());
            throw $th;
        } finally {
            $this->client->close();
        }
    }
}