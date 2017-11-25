<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * GET /cbases
 * 
 * Get cbases.
 */
$app->get('/cbases', function (Request $request, Response $response) {
    $cbases = $this->handler->getCbases();
    foreach ($cbases as &$cbase) {
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
        $usecases = $this->handler->getUsecasesByCbaseId($cbase["id"]);
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
        }
        $cbase["_embedded"] = [
            "usecase" => $usecases
        ];
    }
    return $response->withJson([
        "_links" => [
            "self" => [
                "href" => $request->getUri()->getBaseUrl() . "/cbases"
            ],
            "home" => [
                "href" => $request->getUri()->getBaseUrl()
            ]
        ],
        "_embedded" => [
            "cbase" => $cbases
        ]
    ]);
});

/**
 * GET /cbases/<cbaseId>
 * 
 * Get cbase.
 */
$app->get('/cbases/{cbaseId}', function (Request $request, Response $response) {
    $cbaseId = $request->getAttribute('cbaseId');
    if (is_numeric($cbaseId)) {
        $cbase = $this->handler->getCbaseById($cbaseId);
    } else {
        $cbaseSlug = $cbaseId;
        $cbase = $this->handler->getCbaseBySlug($cbaseSlug);
    }
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
    $usecases = $this->handler->getUsecasesByCbaseId($cbase["id"]);
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
    }
    $cbase["_embedded"] = [
        "usecase" => $usecases
    ];
    return $response->withJson($cbase);
});

/**
 * POST /cbases
 * 
 * Create cbase.
 */
$app->post('/cbases', function (Request $request, Response $response) {
    try {
        $cbase= $this->handler->createCbase($request->getParsedBody());
    } catch (\Exception $e) {
        return $response
            ->withStatus($e->getCode())
            ->withJson([
                "message" => $e->getMessage()
            ]);
    }
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
    $usecases = $this->handler->getUsecasesByCbaseId($cbase["id"]);
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
    }
    $cbase["_embedded"] = [
        "usecase" => $usecases
    ];
    return $response->withJson($cbase);
});
