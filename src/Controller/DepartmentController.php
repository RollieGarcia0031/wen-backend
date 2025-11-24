<?php

namespace App\Controller;

use App\Base\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Http\Cookie;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\DepartmentService;
use PDOException;

class DepartmentController extends Controller {

    /**
     * Allow a professor to join in a department
     * 
     *  - Required fields:
     *      - department_id - id of department
     */
    public function join(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('professor');
        RequestMiddleware::requireFields(['department_id']);

        $user = Cookie::getUser();

        $user_id = $user->id;
        $department_id = Request::getBody()['department_id'];

        try {
            DepartmentService::addUserToDepartment($user_id, $department_id);

            Response::sendJson(200, true, "Success", null);
        } catch (PDOException $error) {
            Response::sendError($error);
        } 
    }

    /**
     * Retrieves all of the departments available
     */
    public function listAll(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('professor');

        try {
            $departments = DepartmentService::getAllDepartments();

            Response::sendJson(200, true, "Success", $departments);
        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }
}