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
    $cbase = json_decode($this->client->post('/cbases', ['json' => $request->getParsedBody()])->getBody(), true);
    return $response->withRedirect($this->router->pathFor('root'));
});

/**
 * GET /admin/cbase/<cbase_slug>/login
 * 
 * Login to cbase admin.
 */
// $app->get('/admin/cbase/{cbase_slug}/login', function (Request $request, Response $response) {
//     $cbase_slug = $request->getAttribute("cbase_slug");
//     $cbase = json_decode($this->client->get('/cbases/' . $cbase_slug)->getBody(), true);
    
//     if (!$cbase) {
//         return $response->withStatus(404);
//     }
    
//     if ($request->getQueryParam("token")) {
//         $token = $request->getQueryParam("token");
//         $token = json_decode($this->client->get('/cbases/' . $cbase_slug . '/token/' . $token)->getBody(), true);
//         var_dump($token); die;
//     }
//     // If token in GET, validate and set to COOKIES and redirect
    
//     // Else if token in COOKIES, validate and redirect
    
//     // If valid token, redirect
    
//     // If no (valid) token, request token e-mail
//     else {
//         $token = json_decode($this->client->post('/cbases/' . $cbase_slug . '/token')->getBody(), true)["token"];
//         $uri = $request->getUri() . "?token={$token}";
        
//         $headers  = "MIME-Version: 1.0\r\n";
//         $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
//         $headers .= "From: no-reply@cbase.eu\r\n" .
//                     "X-Mailer: PHP/" . phpversion();
//         $email_title = "{$cbase["name"]} cbase admin link";
//         $email_body = "
//             <html><body>
//             <h1>CBase \"{$cbase["name"]}\" login</h1>
//             <p>Hi, your cbase \"{$cbase["name"]}\" login. Click on the link to admin your cbase:</p>
//             <p><a href=\"{$uri}\">{$uri}</a></p>
//             </body></html>
//         ";
//         mail($cbase["admin_email"], $email_title, $email_body, $headers);
//     }
    
//     return $this->view->render($response, 'admin.cbase.html', [
//         "cbase" => $cbase
//     ]);
// });

/**
 * GET /admin/cbase/<cbase_slug>
 * 
 * Get cbase admin.
 */
$app->get('/admin/cbase/{cbase_slug}', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $cbase_token = $request->getQueryParam("token");
    $cbase = json_decode($this->client->get('/cbases/' . $cbase_slug)->getBody(), true);
    return $this->view->render($response, 'admin.cbase.html', [
        "cbase" => $cbase
    ]);
})->setName('admin_cbase');

/**
 * GET /admin/cbase/<cbase_slug>/usecase/<usecase_slug>
 * 
 * Get usecase admin.
 */
$app->get('/admin/cbase/{cbase_slug}/usecase/{usecase_slug}', function (Request $request, Response $response) {
    $usecase_slug = $request->getAttribute("usecase_slug");
    $usecase = json_decode($this->client->get('/usecases/' . $usecase_slug)->getBody(), true);
    return $this->view->render($response, 'admin.usecase.html', [
        'usecase' => $usecase
    ]);
})->setName('admin_usecase');

/**
 * POST /admin/cbase/<cbase_slug>/usecase/create
 * 
 * Create usecase within cbase.
 */
$app->post('/admin/cbase/{cbase_slug}/usecase/create', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $usecase = json_decode($this->client->post(
        "/cbases/{$cbase_slug}/usecases",
        ['json' => $request->getParsedBody()]
    )->getBody(), true);
    return $response->withRedirect($this->router->pathFor('admin_usecase', [
        'cbase_slug' => $cbase_slug,
        'usecase_slug' => $usecase["slug"]
    ]));
});

/**
 * POST /admin/cbase/<cbase_slug>/usecase/<usecase_slug>/update
 * 
 * Update usecase.
 */
$app->post('/admin/cbase/{cbase_slug}/usecase/{usecase_slug}/update', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $usecase_slug = $request->getAttribute("usecase_slug");
    $usecase = json_decode($this->client->put(
        '/usecases/' . $usecase_slug,
        ['json' => $request->getParsedBody()]
    )->getBody(), true);
    return $response->withRedirect($this->router->pathFor('admin_usecase', [
        'cbase_slug' => $cbase_slug,
        'usecase_slug' => $usecase["slug"]
    ]));
});

/**
 * POST /admin/cbase/<cbase_slug>/usecase/<usecase_slug>/delete
 * 
 * Delete usecase.
 */
$app->post('/admin/cbase/{cbase_slug}/usecase/{usecase_slug}/delete', function (Request $request, Response $response) {
    $cbase_slug = $request->getAttribute("cbase_slug");
    $usecase_slug = $request->getAttribute("usecase_slug");
    $this->client->delete("/usecases/{$usecase_slug}");
    return $response->withRedirect($this->router->pathFor('admin_cbase', [
        'cbase_slug' => $cbase_slug
    ]));
});
