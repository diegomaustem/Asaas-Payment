<?php 

namespace App\Controllers;

class CobrancasController
{
    private string $metodo;

    public function __construct(string $metodo) 
    {
        $this->metodo = $metodo;
    }

    public function index() 
    {
        return 'Listando cobranças';
    }

    public function store() 
    {
        return 'Criar cobranças';
    }
}