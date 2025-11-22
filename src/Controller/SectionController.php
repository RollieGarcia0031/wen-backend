<?php

namespace App\Controller;

use App\Base\Controller;
use App\Http\Cookie;
use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use PDOException;
use App\Service\SectionService;

class SectionController extends Controller
{

    /**
     * Entroll the logged student to a specific section
     * 
     * - Required Fields:
     *    - section_id - primary key of section
     */
    public function enrollStudent(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(['section_id']);

        $user = Cookie::getUser();
        $role = $user->role;

        $params = Request::getBody();
        $params['user_id'] = $user->id;

        try {
            $result = SectionService::enrollUser($params, $role);

            $message = "Succesfully Enrolled";

            Response::sendJson(200, true, $message, null);
        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }
}