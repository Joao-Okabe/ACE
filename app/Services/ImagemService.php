<?php

class ImagemService
{
    public static function otimizar(string $caminho, int $limite = 800): void
    {
        $info = @getimagesize($caminho);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            throw new Exception('O arquivo enviado não é uma imagem válida.');
        }

        [$largura, $altura] = $info;
        $tipo = $info[2] ?? 0;

        $origem = match ($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($caminho),
            IMAGETYPE_PNG => @imagecreatefrompng($caminho),
            IMAGETYPE_WEBP => @imagecreatefromwebp($caminho),
            default => false,
        };

        if ($origem === false) {
            throw new Exception('Não foi possível processar a imagem enviada.');
        }

        $escala = min(1, $limite / max($largura, $altura));
        $novaLargura = max(1, (int) round($largura * $escala));
        $novaAltura = max(1, (int) round($altura * $escala));
        $destino = imagecreatetruecolor($novaLargura, $novaAltura);

        if ($tipo === IMAGETYPE_PNG) {
            imagealphablending($destino, false);
            imagesavealpha($destino, true);
        }

        imagecopyresampled(
            $destino,
            $origem,
            0,
            0,
            0,
            0,
            $novaLargura,
            $novaAltura,
            $largura,
            $altura
        );

        $salvou = match ($tipo) {
            IMAGETYPE_JPEG => imagejpeg($destino, $caminho, 82),
            IMAGETYPE_PNG => imagepng($destino, $caminho, 8),
            IMAGETYPE_WEBP => imagewebp($destino, $caminho, 82),
            default => false,
        };

        imagedestroy($origem);
        imagedestroy($destino);

        if (!$salvou) {
            throw new Exception('Não foi possível otimizar a imagem enviada.');
        }
    }
}
