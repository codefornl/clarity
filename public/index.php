<?php

//ini_set("error_reporting", E_ALL);
//ini_set("display_errors", 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['twig']['template_dir'] = '../private/templates/';
$config['twig']['debug'] = false;
$config['twig']['cache'] = false; // false || 'path/to/cache'

$config['api']['base_uri'] = getenv('BASE_URI');
$config['api']['timeout'] = 2.0;

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

    $translateFunction = new Twig_SimpleFunction('translate', function ($text, $count=1) {

        if (isset($_GET['l'])) {
            $lang = $_GET['l'];
        } else {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }

        $acceptLang = ['nl', 'es', 'en']; 
        $lang = in_array($lang, $acceptLang) ? $lang : 'en';
        if(file_exists(__DIR__ . "/../private/locale/{$lang}.json")){
            $f = file_get_contents(__DIR__ . "/../private/locale/{$lang}.json");
        } else {
            $f = file_get_contents(__DIR__ . "/../private/locale/en.json");
        }
        $translations = json_decode($f, true);

        if(isset($translations[$text])){
            $lang_arr = explode("|", $translations[$text]);
            if(count($lang_arr) > 1){
                if($count > 1){
                    return $lang_arr[1];
                } else {
                    return $lang_arr[0];
                }
            } else {
                return $lang_arr[0];
            }
        } else {
            return $text;
        }
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

require('../private/routers/cbases.php');
require('../private/routers/usecases.php');
require('../private/routers/admin.php');
require('../private/routers/home.php');

$app->run();
