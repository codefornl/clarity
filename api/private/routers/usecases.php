<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /usecases
 * 
 * Get usecases.
 */
$app->get('/usecases', function (Request $request, Response $response) {
    $usecases = $this->handler->getUsecases();
    foreach ($usecases as &$usecase) {
        $usecase["_links"] = [
            "self" => [
                "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}"
            ],
            "self_slug" => [
                "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}"
            ],
            "usecases" => [
                "href" => $request->getUri()->getBaseUrl() . "/usecases"
            ],
            "home" => [
                "href" => $request->getUri()->getBaseUrl()
            ]
        ];
        $cbase = $this->handler->getCbaseById($usecase["cbase_id"]);
        $cbase["_links"] = [
            "self" => [
                "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}"
            ],
            "self_slug" => [
                "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}"
            ],
            "cbases" => [
                "href" => $request->getUri()->getBaseUrl() . "/cbases"
            ],
            "home" => [
                "href" => $request->getUri()->getBaseUrl()
            ]
        ];
        $usecase["_embedded"] = [
            "cbase" => $cbase
        ];
    }
    return $response->withJson([
        "_links" => [
            "self" => [
                "href" => $request->getUri()->getBaseUrl() . "/usecases"
            ],
            "home" => [
                "href" => $request->getUri()->getBaseUrl()
            ]
        ],
        "_embedded" => [
            "usecase" => $usecases
        ]
    ]);
});

/**
 * GET /usecases/<usecaseId>
 * 
 * Get usecase.
 */
$app->get('/usecases/{usecaseId}', function (Request $request, Response $response) {
    $usecaseId = $request->getAttribute('usecaseId');
    if (is_numeric($usecaseId)) {
        $usecase = $this->handler->getUsecaseById($usecaseId);
    } else {
        $usecaseSlug = $usecaseId;
        $usecase = $this->handler->getUsecaseBySlug($usecaseSlug);
    }
    $usecase["_links"] = [
        "self" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}"
        ],
        "self_slug" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}"
        ],
        "usecases" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases"
        ],
        "home" => [
            "href" => $request->getUri()->getBaseUrl()
        ]
    ];
    $cbase = $this->handler->getCbaseById($usecase["cbase_id"]);
    $cbase["_links"] = [
        "self" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}"
        ],
        "self_slug" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}"
        ],
        "cbases" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases"
        ],
        "home" => [
            "href" => $request->getUri()->getBaseUrl()
        ]
    ];
    $usecase["_embedded"] = [
        "cbase" => $cbase
    ];
    return $response->withJson($usecase);
});

/**
 * POST /cbases/<cbaseId>/usecases
 * 
 * Create usecase within cbase.
 */
$app->post('/cbases/{cbaseId}/usecases', function (Request $request, Response $response) {
    $cbaseId = $request->getAttribute('cbaseId');
    if (is_numeric($cbaseId)) {
        $cbase = $this->handler->getCbaseById($cbaseId);
    } else {
        $cbaseSlug = $cbaseId;
        $cbase = $this->handler->getCbaseBySlug($cbaseSlug);
    }
    $token = $request->getHeader('Authorization')[0];
    if (substr($token, 0, 7) !== "Bearer ") {
        return $response
            ->withStatus(403)
            ->withJson([
                'message' => 'Unable to authorize'
            ]);
    }
    $token = substr($token, 7);
    try {
        $usecase = $this->handler->createUsecaseWithinCbase($cbase, $request->getParsedBody(), $token);
    } catch (\Exception $e) {
        return $response
            ->withStatus($e->getCode())
            ->withJson([
                'message' => $e->getMessage()
            ]);
    }
    $usecase["_links"] = [
        "self" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}"
        ],
        "self_slug" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}"
        ],
        "usecases" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases"
        ],
        "home" => [
            "href" => $request->getUri()->getBaseUrl()
        ]
    ];
    $cbase = $this->handler->getCbaseById($usecase["cbase_id"]);
    $cbase["_links"] = [
        "self" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}"
        ],
        "self_slug" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}"
        ],
        "cbases" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases"
        ],
        "home" => [
            "href" => $request->getUri()->getBaseUrl()
        ]
    ];
    $usecase["_embedded"] = [
        "cbase" => $cbase
    ];
    return $response->withJson($usecase);
});

/**
 * PUT /usecases/<usecaseId>
 * 
 * Update usecase.
 */
$app->put('/usecases/{usecaseId}', function (Request $request, Response $response) {
    $usecaseId = $request->getAttribute('usecaseId');
    if (is_numeric($usecaseId)) {
        $usecase = $this->handler->getUsecaseById($usecaseId);
    } else {
        $usecaseSlug = $usecaseId;
        $usecase = $this->handler->getUsecaseBySlug($usecaseSlug);
    }
    $token = $request->getHeader('Authorization')[0];
    if (substr($token, 0, 7) !== "Bearer ") {
        return $response
            ->withStatus(403)
            ->withJson([
                'message' => 'Unable to authorize'
            ]);
    }
    $token = substr($token, 7);
    try {
        $this->handler->updateUsecase($usecase, $request->getParsedBody(), $token);
    } catch (\Exception $e) {
        return $response
            ->withStatus($e->getCode())
            ->withJson([
                'message' => $e->getMessage()
            ]);
    }
    $usecase["_links"] = [
        "self" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["id"]}"
        ],
        "self_slug" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases/{$usecase["slug"]}"
        ],
        "usecases" => [
            "href" => $request->getUri()->getBaseUrl() . "/usecases"
        ],
        "home" => [
            "href" => $request->getUri()->getBaseUrl()
        ]
    ];
    $cbase = $this->handler->getCbaseById($usecase["cbase_id"]);
    $cbase["_links"] = [
        "self" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["id"]}"
        ],
        "self_slug" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases/{$cbase["slug"]}"
        ],
        "cbases" => [
            "href" => $request->getUri()->getBaseUrl() . "/cbases"
        ],
        "home" => [
            "href" => $request->getUri()->getBaseUrl()
        ]
    ];
    $usecase["_embedded"] = [
        "cbase" => $cbase
    ];
    return $response->withJson($usecase);
});

/**
 * DELETE /usecases/<usecaseId>
 * 
 * Delete usecase
 */
$app->delete('/usecases/{usecaseId}', function (Request $request, Response $response) {
    $usecaseId = $request->getAttribute('usecaseId');
    if (is_numeric($usecaseId)) {
        $usecase = $this->handler->getUsecaseById($usecaseId);
    } else {
        $usecaseSlug = $usecaseId;
        $usecase = $this->handler->getUsecaseBySlug($usecaseSlug);
    }
    $token = $request->getHeader('Authorization')[0];
    if (substr($token, 0, 7) !== "Bearer ") {
        return $response
            ->withStatus(403)
            ->withJson([
                'message' => 'Unable to authorize'
            ]);
    }
    $token = substr($token, 7);
    try {
        $this->handler->deleteUsecase($usecase, $token);
    } catch (\Exception $e) {
        return $response
            ->withStatus($e->getCode())
            ->withJson([
                'message' => $e->getMessage()
            ]);
    }
    return $response->withStatus(204);
});