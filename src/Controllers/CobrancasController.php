<?php 

namespace App\Controllers;

class CobrancasController
{
    private string $metodo;

    public function __construct(string $metodo) 
    {
        $this->metodo = $metodo;
    }

    public function listarCobrancas() 
    {
        return json_encode([
            'message' => 'Listando cobranças',
            'metodo' => $this->metodo
        ]);

    }

    public function criarCobranca() 
    {
        return json_encode([
            'message' => 'Criando cobrança',
            'metodo' => $this->metodo
        ]);
    }
}