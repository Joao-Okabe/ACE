<?php

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $className = ltrim($class, '\\');

            $directories = [
                __DIR__ . '/../Controllers/',
                __DIR__ . '/../Models/',
                __DIR__ . '/../Services/',
                __DIR__ . '/',
            ];

            foreach ($directories as $directory) {
                $files = [
                    $directory . $className . '.php',
                    $directory . lcfirst($className) . '.php',
                    $directory . strtolower($className) . '.php',
                ];

                if (
                    str_ends_with(strtolower($className), 'service')
                    && str_contains($directory, '/Services/')
                ) {
                    $serviceName = substr($className, 0, -7);
                    $files[] = $directory . $serviceName . '.php';
                    $files[] = $directory . lcfirst($serviceName) . '.php';
                    $files[] = $directory . strtolower($serviceName) . '.php';
                }

                foreach (array_unique($files) as $file) {
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }
}
