
<?php

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $className = ltrim($class, '\\');

            // Padrões de diretórios onde as classes podem estar.
            $directoryPatterns = [
                __DIR__ . '/../Modules/*/Controllers',
                __DIR__ . '/../Modules/*/Models',
                __DIR__ . '/../Modules/*/Services',
                __DIR__ . '/../Modules/*/Validators',
                __DIR__ . '/../Middleware',
                __DIR__,
            ];

            $directories = [];

            // Expande os curingas e prepara os caminhos.
            foreach ($directoryPatterns as $pattern) {
                if (str_contains($pattern, '*')) {
                    $matches = glob($pattern, GLOB_ONLYDIR);
                } else {
                    $matches = is_dir($pattern) ? [$pattern] : [];
                }

                if ($matches === false) {
                    continue;
                }

                foreach ($matches as $directory) {
                    $directories[] = rtrim(
                        $directory,
                        DIRECTORY_SEPARATOR
                    ) . DIRECTORY_SEPARATOR;
                }
            }

            // Procura a classe nos diretórios encontrados.
            foreach ($directories as $directory) {
                $files = [
                    $directory . $className . '.php',
                    $directory . lcfirst($className) . '.php',
                    $directory . strtolower($className) . '.php',
                ];

                // Compatibilidade com arquivos de Services.
                if (
                    str_ends_with(strtolower($className), 'service')
                    && str_contains(
                        $directory,
                        DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR
                    )
                ) {
                    $serviceName = substr($className, 0, -7);

                    $files[] = $directory . $serviceName . '.php';
                    $files[] = $directory . lcfirst($serviceName) . '.php';
                    $files[] = $directory . strtolower($serviceName) . '.php';
                }

                // Compatibilidade com arquivos de Models.
                if (
                    str_contains(
                        $directory,
                        DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR
                    )
                ) {
                    $files[] = $directory . $className . 'Model.php';
                    $files[] = $directory . lcfirst($className) . 'Model.php';
                    $files[] = $directory . strtolower($className) . 'model.php';
                }

                // Carrega o primeiro arquivo encontrado.
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
