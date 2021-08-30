#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dufrei\ApiJsonCfdiBridge\Values\Token;

$token = Token::createRandom();

echo implode(PHP_EOL, [
    "Set up the environment with AUTHORIZATION_TOKEN='{$token->getHash()}'",
    'Your client must use the HTTP authorization header:',
    "   Authorization: Bearer {$token->getToken()}",
    '',
]);
