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
            ->withHeader('Content-type', 'application/hal+json,application/json')
            //->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Origin', $req->getHeader('Origin')) // FIXME ?
            ->withHeader('Access-Control-Allow-Credentials', 'true') // FIXME ?
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
    $handler = new \Cbase\Handler($c['db'], $c['settings']['root_pass']);
    return $handler;
};

/**
 * GET /
 * 
 * Get api home.
 */
$app->get('/', function (Request $request, Response $response) {
    return $response->withJson([
        "service" => "cbase: clarity curated sets of use cases",
        "about" => "http://www.cbase.eu",
        "browser" => "http://haltalk.herokuapp.com/explorer/browser.html#http://api.cbase.eu/",
        "application" => "http://app.cbase.eu",
        "codebase" => "https://github.com/codefornl/clarity_slim",
        "_links" => [
            "self" => [
                "href" => $request->getUri()->getBaseUrl()
            ],
            "cbases" => [
                "href" => $request->getUri()->getBaseUrl() . "/cbases"
            ],
            "usecases" => [
                "href" => $request->getUri()->getBaseUrl() . "/usecases"
            ]
        ]
    ]);
});

/**
 * GET /cbases/<cbaseId>/token/<token>
 * 
 * Get cbase token.
 */
$app->get('/cbases/{cbaseId}/token/{token}', function (Request $request, Response $response) {
    $cbaseId = $request->getAttribute('cbaseId');
    $token = $request->getAttribute('token');
    if (is_numeric($cbaseId)) {
        $cbase = $this->handler->getCbaseById($cbaseId);
    } else {
        $cbaseSlug = $cbaseId;
        $cbase = $this->handler->getCbaseBySlug($cbaseSlug);
    }
    $token = $this->handler->getCbaseTokenIfValid($cbase, $token);
    return $response->withJson([
        "token" => $token
    ]);
});

/**
 * POST /cbases/<cbaseId>/token
 * 
 * Create cbase token.
 */
$app->post('/cbases/{cbaseId}/token', function (Request $request, Response $response) {
    $cbaseId = $request->getAttribute('cbaseId');
    if (is_numeric($cbaseId)) {
        $cbase = $this->handler->getCbaseById($cbaseId);
    } else {
        $cbaseSlug = $cbaseId;
        $cbase = $this->handler->getCbaseBySlug($cbaseSlug);
    }
    $token = $this->handler->createCbaseToken($cbase);
    return $response->withJson([
        "token" => $token
    ]);
});

require('../private/routers/cbases.php');
require('../private/routers/usecases.php');

$app->run();
