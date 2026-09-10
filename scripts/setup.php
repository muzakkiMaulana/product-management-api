<?php

$root = dirname(__DIR__);
$path = $root.'/.env';
$contents = file_get_contents(file_exists($path) ? $path : $root.'/.env.example');

foreach (['APP_KEY', 'DB_PASSWORD', 'MYSQL_ROOT_PASSWORD'] as $key) {
    $pattern = '/^'.preg_quote($key, '/').'=(.*)$/m';

    if (preg_match($pattern, $contents, $matches) && trim($matches[1], " \t\r\n\"'") !== '') {
        continue;
    }

    $value = $key === 'APP_KEY' ? 'base64:'.base64_encode(random_bytes(32)) : bin2hex(random_bytes(24));
    $line = $key.'='.$value;
    $contents = preg_match($pattern, $contents)
        ? preg_replace_callback($pattern, fn () => $line, $contents)
        : rtrim($contents).PHP_EOL.$line.PHP_EOL;
}

if (file_put_contents($path, $contents, LOCK_EX) === false || ! chmod($path, 0600)) {
    fwrite(STDERR, "Could not write the environment file.\n");
    exit(1);
}

echo "Environment ready. Existing non-empty values were preserved.\n";
