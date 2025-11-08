<?php

namespace App\Controller;

use App\Http\Cookie;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Http\Request;
use App\Service\NotificationService;
use PDOException;

class NotificationController {
    /**
     * Retrieves the count of all of the unread notifications
     */
    public function countUnread(){
        AuthMiddleware::requireAuth();

        $user_id = Cookie::getUser()->id;
        $params = ["user_id" => $user_id];

        try {
            $results = NotificationService::countUnread($params);

            Response::sendJson(200, true, "Query Success", $results);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Updates all of notification with unread status to
     * seen status
     */
    public function markAllAsRead(){
        AuthMiddleware::requireAuth();

        $user_id = Cookie::getUser()->id;
        $params = ["user_id" => $user_id];

        try {
            NotificationService::markAllAsRead($params);

            Response::sendJson(200, true, "Query Success", null);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Lists all of the unread notifications for the user
     */
    public function listUnread(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["end_from"]);

        $params = Request::getBody();
        
        $user_id = Cookie::getUser()->id;
        $params["user_id"] = $user_id;

        try {
            $results = NotificationService::listUnread($params);

            Response::sendJson(200, true, "Query Success", $results);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Lists all of the notifications for the user
     */
    public function listAll(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["end_from"]);

        $params = Request::getBody();
        
        $user_id = Cookie::getUser()->id;
        $params["user_id"] = $user_id;

        try {
            $results = NotificationService::listAll($params);

            Response::sendJson(200, true, "Query Success", $results);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }
}
