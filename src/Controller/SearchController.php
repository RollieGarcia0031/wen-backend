<?php

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Middleware\RequestMiddleware;
use App\Service\SearchService;
use PDOException;

class SearchController {

    /**
     * Search a list of professor using 
     * user name only without search filter
     */
    public static function searchProfessor(){
        RequestMiddleware::requireFields(['user_name']);

        $param = Request::getBody();

        try {
            $result = SearchService::searchProfessorByName($param);

            Response::sendJson(200, true, "Search Result", $result);
        } catch (PDOException $error) {
            Response::sendError($error);
        } 
    }
}
