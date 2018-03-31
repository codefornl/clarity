<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /
 * 
 * Get cbases.
 */
$app->get('/', function (Request $request, Response $response) {
    $q = $request->getQueryParam("q");
    $result = json_decode($this->client->get('/cbases?q=' . $q)->getBody(), true);
    $cbases = $result["_embedded"]["cbase"];
    $cbases = array_filter($cbases, function ($cbase) {
        return !empty($cbase["_embedded"]["usecase"]);
    });
    $promoted = array_filter($cbases, function ($cbase) {
        return (bool)$cbase["promote"];
    });
    $nonPromoted = array_filter($cbases, function ($cbase) {
        return !$cbase["promote"];
    });
    return $this->view->render($response, 'homepage.html', [
        'cbases' => array_merge($promoted, $nonPromoted),
        'q' => $q,
        'uri' => $request->getUri()
    ]);
});