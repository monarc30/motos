<?php

namespace App\Services;

use Dotenv\Dotenv;

class EnvService
{
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Carrega .env manualmente (raiz do projeto ou .env_prod em produção)
        $basePath = dirname(__DIR__, 2);
        $envFile = $basePath . '/.env';
        if (!file_exists($envFile)) {
            $envFile = $basePath . '/.env_prod';
        }
        if (!file_exists($envFile) && getcwd() !== false) {
            $envFile = rtrim(getcwd(), DIRECTORY_SEPARATOR) . '/.env';
        }
        if (!file_exists($envFile) && getcwd() !== false) {
            $envFile = rtrim(getcwd(), DIRECTORY_SEPARATOR) . '/.env_prod';
        }
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue; // Ignora comentários e linhas vazias
                }
                if (strpos($line, '=') === false) {
                    continue;
                }
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Remove aspas se houver
                $value = trim($value, '"\'');
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
        
        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        self::load();
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

