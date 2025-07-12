<?php 
namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Coroutine\Http\Client;
use App\Services\Asaas\Asaas;
use Swoole\Coroutine\Channel;
use DateTime;
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
        $debtData = json_decode($request->rawContent(), true);
        $validDebt= $this->validateDebtData($debtData);

        if($validDebt !== true) {
            $response->status(422);
            $response->end(json_encode([
                'code' => 422, 
                'error' => 'Validation error in debt data.', 
                'details' => $validDebt
            ]));
            return;
        }

        $channelDebt = new Channel(1);

        go(function () use ($channelDebt, $debtData) {
            try {
                $assasService = new Asaas(new Client($_ENV['API_URL'], PORT, SSL)); 
                $debt = $assasService->createDebt(json_encode($debtData));

                $channelDebt->push($debt);
            }catch(Throwable $th){
                error_log("Log error: " . $th->getMessage());
                throw $th;
            }finally {
                $channelDebt->close();
            }
        });
        $debt = $channelDebt->pop();
        return json_decode($debt);
    }
    
    private function validateDebtData(array $debtData): array|bool
    {
        $errors = [];

        if (!isset($debtData['customer']) || !is_string($debtData['customer']) || trim($debtData['customer']) === '') {
            $errors['customer'] = 'The customer field is required and must be a non-empty string.';
        } elseif (strlen($debtData['customer']) < 3) {
            $errors['customer'] = 'The client must be at least 3 characters long.';
        }

        if (!isset($debtData['billingType']) || !is_string($debtData['billingType']) || trim($debtData['billingType']) === '') {
            $errors['billingType'] = 'The "billingType" field is required and must be a non-empty string.';
        } elseif (strtoupper($debtData['billingType']) !== 'PIX') {
            $errors['billingType'] = 'BillingType must be "PIX".';
        }

        if (!isset($debtData['value']) || !is_numeric($debtData['value'])) {
            $errors['value'] = 'The "value" field is required and must be a number.';
        } elseif ($debtData['value'] <= 0) {
            $errors['value'] = 'The value must be greater than zero.';
        }

        if (!isset($debtData['dueDate']) || !is_string($debtData['dueDate']) || trim($debtData['dueDate']) === '') {
            $errors['dueDate'] = 'The dueDate field is required and must be a non-empty string.';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $debtData['dueDate']);
            if (!$date || $date->format('Y-m-d') !== $debtData['dueDate']) {
                $errors['dueDate'] = 'The dueDate field must be in the format YYYY-MM-DD.';
            } elseif ($date < new DateTime('today')) {
                $errors['dueDate'] = 'The due date cannot be earlier than the current day.';
            }
        }

        return !empty($errors) ? [
            'code' => 422,
            'error' => 'Invalid data.',
            'details' => $errors
        ] : true;
    }
}