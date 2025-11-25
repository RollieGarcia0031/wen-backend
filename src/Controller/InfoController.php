<?php

namespace App\Controller;

use App\Base\Controller;
use App\Http\Cookie;
use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\InfoService;
use PDOException;

class InfoController extends Controller
{
    /**
     * Update a student info in database
     * 
     * - Optional Fields:
     *    - first_name
     *    - last_name
     *    - middle_name
     *    - birthday
     */
    public function updateStudent(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('student');

        $params = Request::getBody();
        $user_id = Cookie::getUser()->id;

        try {
            InfoService::updateStudent($params, $user_id);

            $message = "Update Success";
            Response::sendJson(200, true, $message, null);
        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }

    /**
     * Update a professor info in database
     * 
     * - Optional Fields:
     *     - first_name
     *     - last_name
     *     - middle_name
     *     - birthday
     *     - bio
     *     - gender
     *     - cellphone_number
     */
    public function updateProfessor(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('professor');

        $params = Request::getBody();
        $user_id = Cookie::getUser()->id;

        try {
            InfoService::updateProfessor($params, $user_id);

            $message = "Update Success";
            Response::sendJson(200, true, $message, null);
        } catch (PDOException $error) {
            Response::sendError($error);
        }

    }
}