<?php

namespace App\Controller;

use App\Base\Controller;
use App\Database\Database;
use App\Model\Course;
use App\Http\Request;
use App\Http\Response;
use App\Http\Cookie;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Service\CourseService;
use PDOException;


class CourseController extends Controller {
    public function create(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["name", "description"]);

        $data = Request::getBody();

        [
            "name"       => $name,
            "description"=> $description 
        ] = $data; 

        try {
            $data['created_by'] = $_SESSION['user']->id;

            $course = Course::create($data);

            if ($course->id >= 0){
                Response::sendJson(
                    200,
                    true,
                    "Course Created",
                    [ "id" => $course->id]   
                );
            }

           
        } catch (PDOException $e){
            Response::sendJson(
                500,
                false,
                $e->getMessage(),
                null
            );
        }
    }

    public static function assignToUser(){
        AuthMiddleware::requireAuth();

        $user_id = Cookie::getUser()->id; 
        
        $data = Request::getBody();

        [
            "course_id" => $course_id,
            "year"      => $year,
        ] = $data;

        $data['user_id'] = $user_id;

        try {
            $new_id = CourseService::assign($data);

            Response::sendJson(201, true, "Course Assigned", [
                "new_id" => $new_id
            ]);
        } catch (PDOException $e) {
            Response::sendError($e);             
        }
    } 

    public static function list(){
        try {
            $data = CourseService::getAll();
            Response::sendJson(200, true, "Query Sucess", $data);
        } catch (PDOException $e) {
            Response::sendError($e);
        } 
    }

    public static function search(){
        RequestMiddleware::requireFields(["name"]);

        $data = Request::getBody();

        try {
            $result = CourseService::searchByName($data["name"]);

            Response::sendJson(200, true, "Query Sucess", $result);
        } catch (PDOException $e) {
            Response::sendError($e);
        }
    }

    public static function delete(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["id"]);

        $data = Request::getBody();
        /** id **/
        $user = Cookie::getUser();

        $data["created_by"] = $user->id;

        try {
            $affectedRows = CourseService::deleteById($data);

            if ($affectedRows <= 0){
                Response::sendJson(201, false, "Course Not Found", null);
            }

            Response::sendJson(200, true, "Deleted", [
                "affected_rows" => $affectedRows
            ]); 
        } catch (PDOException $e){
            Response::sendError($e);
        }
    }

    /**
     * Searches for a list of course that is belong to 
     * a certain user
     */
    public static function findUser(){
        RequestMiddleware::requireFields(['user_id']);

        try {
            $param = Request::getBody();

            $result = CourseService::getUserCourseList($param);

            Response::sendJson(
                200, true, "Query Success", $result
            );
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }
}
