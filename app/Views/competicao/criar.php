<?php 
$usuario = $usuario ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Competição</title>
</head>
<body>
    
<form action="POST">
    <label for="nm_competicao"></label>
    <input type="text" value="nm_competicao" name="nm_competicao">

    <label for="dt_inicio"></label>
    <input type="text" value="dt_inicio" name="dt_inicio">

    <label for="dt_encerramento"></label>
    <input type="text" value="dt_encerramento" name="dt_encerramento">
</form>

</body>
</html>