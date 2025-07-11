<?php 
namespace App\Services\Asaas;

use Swoole\Coroutine\Http\Client;
use Throwable;

define('PORT', 443);
define('SSL', TRUE);
define('URL_CUSTOMERS', '/v3/customers');
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
}