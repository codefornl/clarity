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
    $translateFunction = new Twig_SimpleFunction('translate', function ($text, $language) {
        $translations = include(__DIR__ . '/../private/translations.php');
        return $translations[$text][$language] ?
            $translations[$text][$language] :
            $text;
    });
    $view->getEnvironment()->addFunction($translateFunction);
    return $view;
};
$container['client'] = function ($c) {
    $client = new \GuzzleHttp\Client([
        'base_uri' => $c['settings']['api']['base_uri'],
        'timeout'  => $c['settings']['api']['timeout'],
    ]);
    return $client;
};

/**
 * GET /
 * 
 * Get cbases.
 */
$app->get('/', function (Request $request, Response $response) {
    $q = $request->getQueryParam("q");
    $result = json_decode($this->client->get('/cbases?q=' . $q)->getBody(), true);
    $cbases = $result["_embedded"]["cbase"];
    usort($cbases, function ($a, $b) {
        if ($a["promote"] === $b["promote"]) return 0;
        if ($a["promote"]) return -1;
        if ($b["promote"]) return 1;
    });
    return $this->view->render($response, 'homepage.html', [
        'cbases' => $cbases,
        'q' => $q,
        'uri' => $request->getUri()
    ]);
});

require('../private/routers/cbases.php');
require('../private/routers/usecases.php');
require('../private/routers/admin.php');

$app->run();
