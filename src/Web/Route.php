<?php

use App\Controller\AuthController;
use App\Controller\CourseController;
use App\Middleware\AuthMiddleware;
use App\Service\CourseService;

$routes = [];

$routes["POST"] = [
    "/auth/register"    => [ AuthController::class,     "register"      ],
    "/auth/login"       => [ AuthController::class,     "login"         ],

    "/course/create"    => [ CourseController::class,   "create"        ],
    "/course/use"       => [ CourseController::class,   "assignToUser"  ],
    "/course/search"    => [ CourseController::class,   "search"        ] 
];

$routes["GET"] = [
    "/auth/profile"     => [ AuthController::class,     "getProfile"    ],
    "/course/list"      => [ CourseController::class,   "list"          ]
];

$routes["DELETE"] = [
    "/course/delete"    => [ CourseController::class,   "delete"        ]
];
