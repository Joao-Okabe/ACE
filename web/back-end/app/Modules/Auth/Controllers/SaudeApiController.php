<?php

class SaudeApiController
{
    public function show(): void
    {
        Response::success([
            'status' => 'ok',
            'servico' => 'ACE Mobile API',
            'versao' => '1.0.0',
        ], 'Serviço disponível.');
    }
}
