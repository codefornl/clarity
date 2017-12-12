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