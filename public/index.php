<?php

declare(strict_types=1);

use App\Controllers\BuildCfdiFromJsonController;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->post('/build-cfdi-from-json', BuildCfdiFromJsonController::class);

$app->run();
