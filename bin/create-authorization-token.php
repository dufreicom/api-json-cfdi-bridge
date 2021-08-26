<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

$pass = hash('sha1', random_bytes(256));
$hash = password_hash($pass, null);

echo implode(PHP_EOL, [
    "Set up the environment with AUTHORIZATION_KEY=$hash",
    'Your client must use the HTTP authorization header:',
    "   Authorization: Bearer $pass",
    '',
]);
