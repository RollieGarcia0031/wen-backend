<?php

namespace App\Controller;

use App\Http\Cookie;
use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\AvailabilityService;
use PDOException;

class AvailabilityController {
    /**
     * Request body:
     *  - day: 0 | 1 | 2 | 3 | 4 | 5 | 6 |
     *  - time_start: 24:00
     *  - time_end: 24:00
     */
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

    /**
     * Finds a user's availability 
     */
    public static function findUser(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(['user_id']);

        try {
            $param = Request::getBody();
            $list = AvailabilityService::getByUser([
                "user_id" => $param["user_id"]
            ]);

            Response::sendJson(200, true, "Query Success", $list);

        } catch (PDOException $error){
            Response::sendError($error); 
        }
    }
    
    /**
     * Creates multiple availability in one call
     *
     * Body:
     * {
     *      availability_list: [
     *      {
     *          day_of_week: 1-6
     *          start_time: 24:00:00
     *          end_time: 24:00:00
     *      }
     *    ]
     * }
     */
    public static function createAll(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('professor');
        RequestMiddleware::requireFields(['availability_list']);

        $param = Request::getBody();

        $user_id = Cookie::getUser()->id;

        $param["user_id"] = $user_id;

        try {

            $ids = AvailabilityService::createMultiple($param);

            Response::sendJson(200, true, 'Create Success', $ids);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }
}
