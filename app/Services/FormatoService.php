<?php

class FormatoService 
{
    private Formato $formatoModel;

    public function __construct()
    {
        $this->formatoModel = new Formato();
    }

    public function listar(): array
    {
        return $this->formatoModel->listar();
    }
}