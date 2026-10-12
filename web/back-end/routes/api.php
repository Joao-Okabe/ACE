<?php 

$router->get(
    '/api/alunos',
    [AlunoApiController::class, 'index']
);
