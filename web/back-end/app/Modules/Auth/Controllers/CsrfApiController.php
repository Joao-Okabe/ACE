<?php

class CsrfApiController
{
    public function token(): void
    {
        Response::json([
            'sucesso' => true,
            'dados' => [
                'csrf_token' => Csrf::token()
            ]
        ]);
    }
}
