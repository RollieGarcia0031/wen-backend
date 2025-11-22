<?php

use App\Controller\AppointmentController;
use App\Controller\AuthController;
use App\Controller\AvailabilityController;
use App\Controller\CourseController;
use App\Controller\NotificationController;
use App\Controller\SearchController;
use App\Controller\SectionController;

/*
===========================================================

This file contains the associative array of all routes
the exist in the api of backend.

It defines 4 different methods, and pairs the routes with
their corresponding class names of controller and the
name of method to call inside the controller class.

it follows a format of:

"api_route/endpoint" => [ Controller, function_name ]

===========================================================
 */

$routes = [];

$routes["POST"] = [
    "/auth/register"    => [ AuthController::class,     "register"      ],
    "/auth/login"       => [ AuthController::class,     "login"         ],
    "/auth/logout"      => [ AuthController::class,     "logout"        ],

    "/course/create"    => [ CourseController::class,   "create"        ],
    "/course/use"       => [ CourseController::class,   "assignToUser"  ],
    "/course/unuse"     => [ CourseController::class,   "unuse"         ],
    "/course/search"    => [ CourseController::class,   "search"        ],
    "/course/user"      => [ CourseController::class,   "findUser"      ],
    
    "/section/enroll" => [ SectionController::class, "enrollStudent" ],
    "/section/unenroll" => [  SectionController::class, "unenrollUser" ],

    "/availability/create" => [ AvailabilityController::class, "createNew" ],
    "/availability/createAll" =>[AvailabilityController::class,"createAll" ],
    "/availability/user"   => [ AvailabilityController::class, "findUser"  ],

    "/appointment/send"  => [ AppointmentController::class, "send"  ],
    "/appointment/list"  => [ AppointmentController::class, "getOwnList" ],
    "/appointment/accept"=> [ AppointmentController::class, "accept"     ],
    "/appointment/decline" => [ AppointmentController::class, "decline"  ],
    "/appointment/hide"  => [ AppointmentController::class, "hide"       ],

    "/appointment/current-day" => [ AppointmentController::class, "currentDay"],
    "/appointment/count"       => [ AppointmentController::class, "count"     ],

    "/search/professors"  => [ SearchController::class,  "searchProfessor"],
    "/search/professor/user" => [SearchController::class, "searchProfessorUser"],

    "/notification/list/unread" => [ NotificationController::class, "listUnread" ],
    "/notification/list/all" => [ NotificationController::class, "listAll" ]
];

$routes["GET"] = [
    "/auth/profile"     => [ AuthController::class,     "getProfile"    ],

    "/course/list"      => [ CourseController::class,   "list"          ],
    "/course/list/self" => [ CourseController::class,   "selfList"      ],
    "/course/assigned"  => [ CourseController::class,   "getAssigned"   ],

    "/section/list/all" => [ SectionController::class, "getAll" ],
    "/section/list/owned"     => [ SectionController::class, "getOwned" ],

    "/availability/list"=> [ AvailabilityController::class, "getOwnList" ],

    "/notification/count/unread" => [ NotificationController::class, "countUnread"],
    "/notification/mark-all-read" =>[ NotificationController::class, "markAllAsRead"]
];

$routes["DELETE"] = [
    "/course/delete"    => [ CourseController::class,   "delete"        ],

    "/availability/delete"=> [ AvailabilityController::class, "delete"  ],

    "/appointment/delete" => [ AppointmentController::class, "delete"  ],

    "/notification/delete-all" => [ NotificationController::class, "deleteAll"]
];

$routes["PUT"] = [
    "/appointment/message/update" => [ AppointmentController::class, "updateMessage"  ]
];
