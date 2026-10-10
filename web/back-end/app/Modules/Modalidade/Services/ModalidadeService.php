<?php

class ModalidadeService 
{
    private Modalidade $modalidadeModel;

    public function __construct()
    {
        $this->modalidadeModel = new Modalidade();
    }

    public function listar(): array
    {
        return $this->modalidadeModel->listar();
    }
}