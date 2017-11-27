<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /cbase/<cbase_slug>
 * 
 * Get cbase.
 */
$app->get('/cbase/{cbase_slug}', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $q = $request->getQueryParam("q");
    $cbase = json_decode($this->client->get('/cbases/' . $cbase_slug . "?q={$q}")->getBody(), true);
    return $this->view->render($response, 'cbase.html', [
        'cbase' => $cbase,
        'q' => $q
    ]);
});
