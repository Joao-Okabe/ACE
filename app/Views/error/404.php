<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Página não encontrada - 404</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!--CSS-->
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <style>
        * {
            padding: 0px;
            margin: 0px;
        }
        body { 
            font-family: Arial,sans-serif;
            text-align: center;
            align-items: center;
            background-color: #181847;
            color: #333;
        }
        h1 { 
            font-size: 50px;
            color: #F44E1C;
        }
        p {
            font-size: 18px;
        }
        .card{
            outline: none;
            width: 40%;
            height: 30%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        a { 
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #F44E1C;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover { 
            background: #fff;
            outline: 3px solid #F44E1C;
            color: #F44E1C;
        }
        .img-size{
            width: 200px;
            height: 150px;
            object-fit: cover;
        }
        .img-size img{
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="card">
            <h1>Erro 404</h1>
            <p>Ops! A página que você está procurando não existe ou foi movida.</p>
            <p>Acho que você se perdeu...</p>
            <a href="/">Voltar para a Página Inicial</a>
            <div class="logo">
                <img src="/img/logo-ace-laranja.png" alt="Logo ACE">
            </div>
    </div>
</body>
</html>