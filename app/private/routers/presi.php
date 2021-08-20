<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /cbase/<cbase_slug>/presi
 * 
 * Get cbase in presentation mode.
 */
$app->get('/cbase/{cbase_slug}/presi', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $page = (int) $request->getQueryParam("page");
    $cbase = json_decode($this->client->get('/cbases/' . $cbase_slug)->getBody(), true);
    return $this->view->render($response, 'presi.html', [
        'cbase' => $cbase,
        'usecase' => $cbase["_embedded"]["usecase"][$page],
        'prev' => $cbase["_embedded"]["usecase"][$page - 1] ? $page - 1 : $page,
        'next' => $cbase["_embedded"]["usecase"][$page + 1] ? $page + 1 : $page,
        'uri' => $request->getUri()
    ]);
});
