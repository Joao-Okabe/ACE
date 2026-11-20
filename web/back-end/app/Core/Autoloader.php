<?php

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $className = ltrim($class, '\\');

            $directoryPatterns = [
                __DIR__ . '/../Modules/*/Controllers',
                __DIR__ . '/../Modules/*/Models',
                __DIR__ . '/../Modules/*/Services',
                __DIR__ . '/../Modules/*/Validators',
                __DIR__ . '/../Modules/*/Middleware',
                __DIR__ . '/../Middleware',
                __DIR__,
            ];

            $directories = [];

            foreach ($directoryPatterns as $pattern) {
                $matches = str_contains($pattern, '*')
                    ? glob($pattern, GLOB_ONLYDIR)
                    : (is_dir($pattern) ? [$pattern] : []);

                if ($matches === false) {
                    continue;
                }

                foreach ($matches as $directory) {
                    $directories[] = rtrim($directory, DIRECTORY_SEPARATOR)
                        . DIRECTORY_SEPARATOR;
                }
            }

            foreach ($directories as $directory) {
                $files = [
                    $directory . $className . '.php',
                    $directory . lcfirst($className) . '.php',
                    $directory . strtolower($className) . '.php',
                ];

                if (
                    str_ends_with(strtolower($className), 'service')
                    && str_contains($directory, DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR)
                ) {
                    $serviceName = substr($className, 0, -7);
                    $files[] = $directory . $serviceName . '.php';
                    $files[] = $directory . lcfirst($serviceName) . '.php';
                    $files[] = $directory . strtolower($serviceName) . '.php';
                }

                if (str_contains($directory, DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR)) {
                    $files[] = $directory . $className . 'Model.php';
                    $files[] = $directory . lcfirst($className) . 'Model.php';
                    $files[] = $directory . strtolower($className) . 'model.php';
                }

                foreach (array_unique($files) as $file) {
                    if (is_file($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }
}