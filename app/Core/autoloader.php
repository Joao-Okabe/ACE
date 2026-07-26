<?php

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function ($class) {

            $directories = [
                __DIR__ . '/../Controllers/',
                __DIR__ . '/../Models/',
                __DIR__ . '/../Services/',
                __DIR__ . '/',
            ];

            foreach ($directories as $directory) {
                $files = [
                    $directory . $class . '.php',
                    $directory . strtolower($class) . '.php',
                ];

                if (
                    str_ends_with(strtolower($class), 'service')
                    && str_contains($directory, '/Services/')
                ) {
                    $serviceName = substr($class, 0, -7);
                    $files[] = $directory . strtolower($serviceName) . '.php';
                }

                foreach ($files as $file) {
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }
}
