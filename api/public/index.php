<?php

//ini_set("error_reporting", E_ALL);
//ini_set("display_errors", 1);

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
        "service" => "cbase: curated sets of case studies of digital tools for government",
        "about" => "https://www.codefor.nl/clarity",
        "browser" => "http://haltalk.herokuapp.com/explorer/browser.html#https://cbase.codefor.nl/",
        "application" => "https://clarity.codefor.nl",
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
 * GET /token
 * 
 * Get token pair.
 */
$app->get('/token', function (Request $request, Response $response) {
    return $response->withJson($this->handler->createTokenPair());
});

require('../private/routers/cbases.php');
require('../private/routers/usecases.php');

$app->run();
