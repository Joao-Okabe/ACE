<?php

function asset(string $path): string
{
    // If already absolute URL, return as-is
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    // If relative path starting with ./ or ../ return as-is
    if (preg_match('#^\.\.?/#', $path)) {
        return $path;
    }

    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    if ($base === '.' || $base === '/') {
        $base = '';
    }

    return $base . '/' . ltrim($path, '/');
}

function upload_url(?string $storedPath): string
{
    if (empty($storedPath)) {
        return '/img/perfil.jpg';
    }

    // If it's an absolute URL, return
    if (preg_match('#^https?://#i', $storedPath)) {
        return $storedPath;
    }

    // If path starts with ./ or ../ return as-is (local fallback)
    if (preg_match('#^\.\.?/#', $storedPath)) {
        return $storedPath;
    }

    // If starts with slash, treat as public-rooted path
    if (strpos($storedPath, '/') === 0) {
        return asset(ltrim($storedPath, '/'));
    }

    // Otherwise assume it's already a public relative path (eg: uploads/xxx)
    return asset($storedPath);
}

function renderView(string $view, array $data = []): void
{
    $usuario = $_SESSION['usuario'] ?? null;
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../Views/' . $view . '.php';
}
