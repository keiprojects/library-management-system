<?php

declare(strict_types=1);

/**
 * Parses KEY=VALUE lines from a .env file.
 *
 * @return array<string,string>
 */
function parse_env_lines(string $content): array
{
    $values = [];
    $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $separatorPosition = strpos($line, '=');

        if ($separatorPosition === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separatorPosition));
        $value = trim(substr($line, $separatorPosition + 1));

        if ($key === '') {
            continue;
        }

        if (
            strlen($value) >= 2
            && (($value[0] === '"' && $value[strlen($value) - 1] === '"')
            || ($value[0] === "'" && $value[strlen($value) - 1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}

/**
 * Loads .env values into the current request without overriding real environment variables.
 */
function load_env_file(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        return;
    }

    foreach (parse_env_lines($contents) as $key => $value) {
        if (getenv($key) !== false || isset($_ENV[$key], $_SERVER[$key])) {
            continue;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

/**
 * Returns an environment value with a fallback.
 */
function env_value(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}
