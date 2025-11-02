<?php

use App\Controller\AppointmentController;
use App\Controller\AuthController;
use App\Controller\AvailabilityController;
use App\Controller\CourseController;
use App\Controller\SearchController;
use App\Model\Appointment;

$routes = [];

$routes["POST"] = [
    "/auth/register"    => [ AuthController::class,     "register"      ],
    "/auth/login"       => [ AuthController::class,     "login"         ],

    "/course/create"    => [ CourseController::class,   "create"        ],
    "/course/use"       => [ CourseController::class,   "assignToUser"  ],
    "/course/unuse"     => [ CourseController::class,   "unuse"         ],
    "/course/search"    => [ CourseController::class,   "search"        ],
    "/course/user"      => [ CourseController::class,   "findUser"      ],  

    "/availability/create" => [ AvailabilityController::class, "createNew" ],
    "/availability/createAll" =>[AvailabilityController::class,"createAll" ],
    "/availability/user"   => [ AvailabilityController::class, "findUser"  ],

    "/appointment/send"  => [ AppointmentController::class, "createNew"  ],
    "/appointment/list"  => [ AppointmentController::class, "getOwnList" ],
    "/appointment/accept"=> [ AppointmentController::class, "accept"     ],

    "/search/professors"  => [ SearchController::class,  "searchProfessor"]
];

$routes["GET"] = [
    "/auth/profile"     => [ AuthController::class,     "getProfile"    ],

    "/course/list"      => [ CourseController::class,   "list"          ],
    "/course/list/self" => [ CourseController::class,   "selfList"      ],
    "/course/assigned"  => [ CourseController::class,   "getAssigned"   ],

    "/availability/list"=> [ AvailabilityController::class, "getOwnList" ]
];

$routes["DELETE"] = [
    "/course/delete"    => [ CourseController::class,   "delete"        ],

    "/availability/delete"=> [ AvailabilityController::class, "delete"  ],

    "/appointment/delete" => [ AppointmentController::class, "delete"  ]
];

$routes["PUT"] = [
    "/appointment/message/update" => [ AppointmentController::class, "updateMessage"  ]
];
