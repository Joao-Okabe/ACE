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
                    $directory . $class . '.php',
                ];

                if (
                    str_ends_with($class, 'service')
                    && str_contains($directory, '/Services/')
                ) {
                    $serviceName = substr($class, 0, -7);
                    $files[] = $directory . $serviceName . '.php';
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
