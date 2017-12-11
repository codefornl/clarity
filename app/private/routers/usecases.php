<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /cbase/<cbase_slug>/usecase/<usecase_slug>
 * 
 * Get usecase.
 */
$app->get('/cbase/{cbase_slug}/usecase/{usecase_slug}', function (Request $request, Response $response) {
    $usecase_slug = $request->getAttribute("usecase_slug");
    $usecase = json_decode($this->client->get('/usecases/' . $usecase_slug)->getBody(), true);
    return $this->view->render($response, 'usecase.html', [
        'usecase' => $usecase,
        'uri' => $request->getUri()
    ]);
});