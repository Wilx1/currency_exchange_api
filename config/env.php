<?php
function loadEnv($path) {
    if (!file_exists($path)) {
        die(".env file not found at: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignore comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split KEY=VALUE pairs
        list($name, $value) = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        // Store as environment variable
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}
