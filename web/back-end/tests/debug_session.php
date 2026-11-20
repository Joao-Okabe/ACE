<?php
// Endpoint de debug temporário — remover após uso
session_start();
require __DIR__ . '/../app/Core/View.php';
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Debug sessão</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:16px}pre{background:#f5f5f5;padding:12px;border-radius:6px}</style>
</head>
<body>
    <h2>Debug de sessão</h2>
    <h3>$_SESSION</h3>
    <pre><?php echo htmlspecialchars(var_export($_SESSION, true), ENT_QUOTES, 'UTF-8'); ?></pre>

    <h3>Arquivos em /uploads</h3>
    <ul>
        <?php
        $files = glob(__DIR__ . '/uploads/*');
        if ($files === false) {
            echo '<li>Erro ao ler uploads</li>';
        } elseif (empty($files)) {
            echo '<li>Nenhum arquivo em uploads</li>';
        } else {
            foreach ($files as $f) {
                $name = basename($f);
                $url = upload_url('/uploads/' . $name);
                echo '<li>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ' — <a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</a><br><img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" style="max-width:240px;margin-top:8px;border:1px solid #ccc;padding:4px;border-radius:4px"></li>';
            }
        }
        ?>
    </ul>

    <h3>Teste rápido de `upload_url()`</h3>
    <pre><?php
        echo 'upload_url("/uploads/example.png") -> ' . upload_url('/uploads/example.png') . "\n";
        echo 'upload_url("uploads/example.png") -> ' . upload_url('uploads/example.png') . "\n";
    ?></pre>

    <p style="color:#666;font-size:90%">Arquivo temporário de debug — remova quando finalizar.</p>
</body>
</html>
