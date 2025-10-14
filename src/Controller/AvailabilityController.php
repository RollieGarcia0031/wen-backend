<?php

namespace App\Controller;

use App\Http\Cookie;
use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\AvailabilityService;
use PDO;
use PDOException;

class AvailabilityController {
    public static function createNew(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        RequestMiddleware::requireFields([
            "day",
            "time_start",
            "time_end"
        ]);

        $user_id = Cookie::getUser()->id;
        $data = Request::getBody();

        $time_start = $data['time_start'] ?? null;
        $time_end   = $data['time_end'] ?? null;
        $day        = $data['day'] ?? null;

        try {
            $newId = AvailabilityService::create(
                $user_id,
                $time_start,
                $time_end,
                $day
            );

            if ($newId >= 0){
                Response::sendJson(
                    201, true,
                    "Create Sucess",
                    ["new_id" => $newId]
                );
            }

            Response::sendJson(
                300, false,
                "No Id returned", null
            );

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Searches for list of availability of the logged user
     * with a role of professor
     */
    public static function getOwnList(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        
        try {
            $user_id = Cookie::getUser()->id;
            
            $list = AvailabilityService::getByUser([
                'user_id' => $user_id
            ]);

            Response::sendJson(
                200, true,
                "Query Sucess", $list
            );
              
        } catch (PDOException $error){
            Response::sendError($error);
        } 
    }

    /**
     * Deletes a specific availability owned by
     * the logged user
     */
    public static function delete(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        RequestMiddleware::requireFields(["id"]);

        try {
            $param = Request::getBody();
            $param['user_id'] = Cookie::getUser()->id;

            $affectedRows = AvailabilityService::deleteById($param);

            if ($affectedRows > 0){
                Response::sendJson(203, false, "Deleted", null);
            } else {
                Response::sendJson(
                    400, false, "Not Found/No Rows Affected"
                );
            }
         
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }



}
