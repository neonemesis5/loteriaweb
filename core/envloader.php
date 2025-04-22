<?php
namespace Core;

class EnvLoader
{
    /**
     * Carga las variables de entorno desde un archivo .env
     * y las almacena en $_ENV
     *
     * @param string $filePath Ruta absoluta o relativa al archivo .env
     * @return void
     */
    public static function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Archivo .env no encontrado en: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios y líneas vacías
            if (str_starts_with(trim($line), '#') || strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Eliminar comillas si existen
            $value = trim($value, '"\''); 

            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }

        }
    }
}
