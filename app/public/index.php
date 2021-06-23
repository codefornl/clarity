<?php

//ini_set("error_reporting", E_ALL);
//ini_set("display_errors", 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

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
    $view->addExtension(new \Twig_Extensions_Extension_Array());
    $translateFunction = new Twig_SimpleFunction('translate', function ($text, $language) {
        $translations = include(__DIR__ . '/../private/translations.php');
        return $translations[$text][$language] ?
            $translations[$text][$language] :
            $text;
    });
    $view->getEnvironment()->addFunction($translateFunction);
    $engine = new MarkdownEngine\MichelfMarkdownEngine();
    $view->addExtension(new MarkdownExtension($engine));
    return $view;
};
$container['client'] = function ($c) {
    $client = new \GuzzleHttp\Client([
        'base_uri' => $c['settings']['api']['base_uri'],
        'timeout'  => $c['settings']['api']['timeout'],
    ]);
    return $client;
};

require('../private/routers/cbases.php');
require('../private/routers/usecases.php');
require('../private/routers/admin.php');
require('../private/routers/home.php');

$app->run();
