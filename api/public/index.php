<?php

ini_set("error_reporting", E_ALL);
ini_set("display_errors", 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../private/config.php';
$app = new \Slim\App([
    "settings" => $config
]);

// enable CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// register containers
$container = $app->getContainer();
$container['db'] = function($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'] . ";charset=utf8",
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
$container['handler'] = function($c) {
    $handler = new \Cbase\Handler($c['db']);
    return $handler;
};

// routing
$app->get('/', function (Request $request, Response $response) {
    return $response->withJson([
        "service" => "clarity cbase"
    ]);
});

$app->get('/cbases', function (Request $request, Response $response) {
    $cbases = $this->handler->getCbases();
    foreach ($cbases as &$cbase) {
        $cbase["usecases"] = $this->handler->getUsecasesByCbaseId($cbase["id"]);
    }
    return $response->withJson([
        "cbases" => $cbases
    ]);
});

$app->get('/cbases/{cbaseId}', function (Request $request, Response $response) {
    return $response->withJson([
        "cbase" => $this->handler->getCbaseById($request->getAttribute('cbaseId'))
    ]);
});

$app->get('/usecases', function (Request $request, Response $response) {
    return $response->withJson([
        "usecases" => $this->handler->getUsecases()
    ]);
});

$app->get('/usecases/{usecaseId}', function (Request $request, Response $response) {
    return $response->withJson([
        "usecase" => $this->handler->getUsecaseById($request->getAttribute('usecaseId'))
    ]);
});

$app->post('/usecases', function (Request $request, Response $response) {
    return $response->withJson([
        "usecase" => $this->handler->postUsecase($request->getParsedBody())
    ]);
});

$app->run();
