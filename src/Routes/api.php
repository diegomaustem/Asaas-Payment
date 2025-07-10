<?php
namespace App\Routes;

use App\Controllers\CustomerController;
use App\Controllers\DebtController;

$router->get('/listCustomers', CustomerController::class, 'index');
$router->post('/createCustomer', CustomerController::class, 'store');
$router->get('/listDebts', DebtController::class, 'index');
$router->get('/createDebt', DebtController::class, 'store');
