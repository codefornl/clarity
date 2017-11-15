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

// register containers
$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig($c['settings']['twig']['template_dir'], [
        'debug' => $c['settings']['twig']['debug'],
        'cache' => $c['settings']['twig']['cache']
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension(
        $c['router'],
        $basePath));
    $view->addExtension(new \Twig_Extension_Debug());
    return $view;
};
$container['client'] = function ($c) {
    $client = new \GuzzleHttp\Client([
        'base_uri' => $c['settings']['api']['base_uri'],
        'timeout'  => $c['settings']['api']['timeout'],
    ]);
    return $client;
};

// routing
$app->get('/', function (Request $request, Response $response) {
    $cbases = json_decode($this->client->get('/cbases')->getBody(), true)["cbases"];
    return $this->view->render($response, 'homepage.html', [
        'cbases' => $cbases
    ]);
});

$app->get('/edit/{cbase_token}', function (Request $request, Response $response) {
    $cbase_token = $request->getAttribute("cbase_token");
    //$usecase = json_decode($this->client->get('/usecases/' . $usecase_slug)->getBody(), true)["usecase"];
    return $this->view->render($response, 'edit.html', [
        'cbase_token' => $cbase_token
    ]);
});

$app->get('/cbase/{cbase_slug}', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $cbase = json_decode($this->client->get('/cbases/' . $cbase_slug)->getBody(), true)["cbase"];
    return $this->view->render($response, 'cbase.html', [
        'cbase' => $cbase
    ]);
});

$app->get('/cbase/{cbase_slug}/usecase/{usecase_slug}', function (Request $request, Response $response) {
    $usecase_slug = $request->getAttribute("usecase_slug");
    $usecase = json_decode($this->client->get('/usecases/' . $usecase_slug)->getBody(), true)["usecase"];
    return $this->view->render($response, 'usecase.html', [
        'usecase' => $usecase
    ]);
});

$app->run();
