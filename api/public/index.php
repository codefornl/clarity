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
            ->withHeader('Content-type', 'application/hal+json')
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
        "service" => "clarity cbase",
        "_links" => [
            "self" => $request->getUri()->getBaseUrl(),
            "cbases" => $request->getUri()->getBaseUrl() . "/cbases",
            "usecases" => $request->getUri()->getBaseUrl() . "/usecases"
        ]
    ]);
});

$app->get('/cbases', function (Request $request, Response $response) {
    $cbases = $this->handler->getCbases();
    foreach ($cbases as &$cbase) {
        $cbase["_links"] = [
            "self" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}",
            "self_slug" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}",
            "cbases" => $request->getUri()->getBaseUrl() . "/cbases",
            "home" => $request->getUri()->getBaseUrl()
        ];
        $usecases = $this->handler->getUsecasesByCbaseId($cbase["id"]);
        foreach ($usecases as &$usecase) {
            $usecase["_links"] = [
                "self" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}",
                "self_slug" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}",
                "usecases" => $request->getUri()->getBaseUrl() . "/usecases",
                "home" => $request->getUri()->getBaseUrl()
            ];
        }
        $cbase["_embedded"] = [
            "usecase" => $usecases
        ];
    }
    return $response->withJson([
        "cbases" => $cbases
    ]);
});

$app->get('/cbases/{cbaseId}', function (Request $request, Response $response) {
    $cbaseId = $request->getAttribute('cbaseId');
    if (is_numeric($cbaseId)) {
        $cbase = $this->handler->getCbaseById($cbaseId);
    } else {
        $cbaseSlug = $cbaseId;
        $cbase = $this->handler->getCbaseBySlug($cbaseSlug);
    }
    $cbase["_links"] = [
        "self" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}",
        "self_slug" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}",
        "cbases" => $request->getUri()->getBaseUrl() . "/cbases",
        "home" => $request->getUri()->getBaseUrl()
    ];
    $usecases = $this->handler->getUsecasesByCbaseId($cbase["id"]);
    foreach ($usecases as &$usecase) {
        $usecase["_links"] = [
            "self" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}",
            "self_slug" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}",
            "usecases" => $request->getUri()->getBaseUrl() . "/usecases",
            "home" => $request->getUri()->getBaseUrl()
        ];
    }
    $cbase["_embedded"] = [
        "usecase" => $usecases
    ];
    return $response->withJson([
        "cbase" => $cbase
    ]);
});

$app->get('/usecases', function (Request $request, Response $response) {
    $usecases = $this->handler->getUsecases();
    foreach ($usecases as &$usecase) {
        $usecase["_links"] = [
            "self" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}",
            "self_slug" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}",
            "usecases" => $request->getUri()->getBaseUrl() . "/usecases",
            "home" => $request->getUri()->getBaseUrl()
        ];
        $cbase = $this->handler->getCbaseById($usecase["cbase_id"]);
        $cbase["_links"] = [
            "self" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}",
            "self_slug" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}",
            "cbases" => $request->getUri()->getBaseUrl() . "/cbases",
            "home" => $request->getUri()->getBaseUrl()
        ];
        $usecase["_embedded"] = [
            "cbase" => $cbase
        ];
    }
    return $response->withJson([
        "usecases" => $usecases
    ]);
});

$app->get('/usecases/{usecaseId}', function (Request $request, Response $response) {
    $usecaseId = $request->getAttribute('usecaseId');
    if (is_numeric($usecaseId)) {
        $usecase = $this->handler->getUsecaseById($usecaseId);
    } else {
        $usecaseSlug = $usecaseId;
        $usecase = $this->handler->getUsecaseBySlug($usecaseSlug);
    }
    $usecase["_links"] = [
        "self" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}",
        "self_slug" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}",
        "usecases" => $request->getUri()->getBaseUrl() . "/usecases",
        "home" => $request->getUri()->getBaseUrl()
    ];
    $cbase = $this->handler->getCbaseById($usecase["cbase_id"]);
    $cbase["_links"] = [
        "self" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}",
        "self_slug" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}",
        "cbases" => $request->getUri()->getBaseUrl() . "/cbases",
        "home" => $request->getUri()->getBaseUrl()
    ];
    $usecase["_embedded"] = [
        "cbase" => $cbase
    ];
    return $response->withJson([
        "usecase" => $usecase
    ]);
});

$app->post('/usecases', function (Request $request, Response $response) {
    return $response->withJson([
        "usecase" => $this->handler->postUsecase($request->getParsedBody())
    ]);
});

$app->run();
