<?php

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\SearchService;
use PDOException;

class SearchController {

    /**
     * Search a list of professor using 
     * user name only without search filter
     */
    public function searchProfessor(){
        RequestMiddleware::requireFields(['user_name']);

        $param = Request::getBody();

        try {
            $result = SearchService::searchProfessorByName($param);

            Response::sendJson(200, true, "Search Result", $result);
        } catch (PDOException $error) {
            Response::sendError($error);
        } 
    }

    /**
     * Retrieves info about a professor, with provided user_id of
     * professor.
     *
     * This will return information about availability, and classes
     * that a professor is teaching
     */
    public function searchProfessorUser(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('student');
        RequestMiddleware::requireFields(['professor_user_id']);

        $params = Request::getBody();

        try {
            $result = SearchService::searchUserInfo($params);

            Response::sendJson(200, true, 'Search Returned', $result);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }
}
