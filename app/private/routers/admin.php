<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /admin
 * 
 * Admin.
 */
$app->get('/admin', function (Request $request, Response $response) {
    return $this->view->render($response, 'admin.html');
})->setName('root');

/**
 * POST /admin/cbase/create
 * 
 * Create cbase.
 */
$app->post('/admin/cbase/create', function (Request $request, Response $response) {
    // FIXME move root_pass to parameter instead?
    $cbase = json_decode($this->client->post('/cbases', ['json' => $request->getParsedBody()])->getBody(), true);
    return $response->withRedirect($this->router->pathFor(
        'admin_cbase',
        [
            'cbase_slug' => $cbase["slug"]
        ],
        [
            'token' => $cbase["token"]
        ]
    ));
});

/**
 * POST /admin/cbase/<cbase_slug>/update
 * 
 * Update cbase.
 */
$app->post('/admin/cbase/{cbase_slug}/update', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $token = $request->getQueryParam("token");
    $cbase = json_decode($this->client->put(
        '/cbases/' . $cbase_slug,
        [
            'json' => $request->getParsedBody(),
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]
    )->getBody(), true);
    return $response->withRedirect($this->router->pathFor(
        'admin_cbase',
        [
            'cbase_slug' => $cbase["slug"]
        ],
        [
            'token' => $token
        ]
    ));
});

/**
 * GET /admin/cbase/<cbase_slug>
 * 
 * Get cbase admin.
 */
$app->get('/admin/cbase/{cbase_slug}', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $token = $request->getQueryParam("token");
    $cbase = json_decode($this->client->get('/cbases/' . $cbase_slug)->getBody(), true);
    return $this->view->render(
        $response,
        'admin.cbase.html',
        [
            "cbase" => $cbase,
            'token' => $token
        ]
    );
})->setName('admin_cbase');

/**
 * GET /admin/cbase/<cbase_slug>/usecase/<usecase_slug>
 * 
 * Get usecase admin.
 */
$app->get('/admin/cbase/{cbase_slug}/usecase/{usecase_slug}', function (Request $request, Response $response) {
    $usecase_slug = $request->getAttribute("usecase_slug");
    $token = $request->getQueryParam("token");
    $usecase = json_decode($this->client->get('/usecases/' . $usecase_slug)->getBody(), true);
    return $this->view->render(
        $response,
        'admin.usecase.html',
        [
            'usecase' => $usecase,
            'token' => $token
        ]
    );
})->setName('admin_usecase');

/**
 * POST /admin/cbase/<cbase_slug>/usecase/create
 * 
 * Create usecase within cbase.
 */
$app->post('/admin/cbase/{cbase_slug}/usecase/create', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $token = $request->getQueryParam("token");
    $usecase = json_decode($this->client->post(
        "/cbases/{$cbase_slug}/usecases",
        [
            'json' => $request->getParsedBody(),
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]
    )->getBody(), true);
    return $response->withRedirect($this->router->pathFor(
        'admin_usecase',
        [
            'cbase_slug' => $cbase_slug,
            'usecase_slug' => $usecase["slug"]
        ],
        [
            'token' => $token
        ]
    ));
});

/**
 * POST /admin/cbase/<cbase_slug>/usecase/<usecase_slug>/update
 * 
 * Update usecase.
 */
$app->post('/admin/cbase/{cbase_slug}/usecase/{usecase_slug}/update', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $usecase_slug = $request->getAttribute("usecase_slug");
    $token = $request->getQueryParam("token");
    $usecase = json_decode($this->client->put(
        '/usecases/' . $usecase_slug,
        [
            'json' => $request->getParsedBody(),
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]
    )->getBody(), true);
    return $response->withRedirect($this->router->pathFor(
        'admin_usecase',
        [
            'cbase_slug' => $cbase_slug,
            'usecase_slug' => $usecase["slug"]
        ],
        [
            'token' => $token
        ]
    ));
});

/**
 * POST /admin/cbase/<cbase_slug>/usecase/<usecase_slug>/delete
 * 
 * Delete usecase.
 */
$app->post('/admin/cbase/{cbase_slug}/usecase/{usecase_slug}/delete', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $usecase_slug = $request->getAttribute("usecase_slug");
    $token = $request->getQueryParam("token");
    $this->client->delete(
        "/usecases/{$usecase_slug}",
        [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]
    );
    return $response->withRedirect($this->router->pathFor(
        'admin_cbase',
        [
            'cbase_slug' => $cbase_slug
        ],
        [
            'token' => $token
        ]
    ));
});
