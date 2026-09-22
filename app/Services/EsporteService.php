<?php

class EsporteService 
{
    private Esporte $esporteModel;

    public function __construct()
    {
        $this->esporteModel = new Esporte();
    }

    public function listar(): array
    {
        return $this->esporteModel->listar();
    }
}