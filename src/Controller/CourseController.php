<?php

namespace App\Controller;

use App\Base\Controller;
use App\Model\Course;
use App\Http\Request;
use App\Http\Response;
use App\Http\Cookie;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Service\CourseService;
use PDOException;

class CourseController extends Controller {

    /**
     * Allows users to create a new course, that can be used
     * by themselves or other users
     *
     * Required fields:
     *  - name        - name of course
     *  - description - description/long name for the course
     */
    public function create(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["name", "description"]);

        $currentUser = Cookie::getUser();
        $data = Request::getBody();

        $data['created_by'] = $currentUser->id;

        try {

            $course = Course::create($data);

            $message = "Course created successfully";
            $data = [ "id" => $course->id ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Assigns a course to the logged user
     *
     * Required fields:
     *   - year      - year of class, (1,2,3,4)
     *   - course_id - id of course to be assigned
     */
    public static function assignToUser(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(['year', 'course_id']);

        $user_id = Cookie::getUser()->id; 
        
        $data = Request::getBody();

        $data['user_id'] = $user_id;

        try {
            $new_id = CourseService::assign($data);

            $message = "Course has beed assigned";
            $data = [ "new_id" => $new_id ];

            Response::sendJson(200, true, $message, $data);
        } catch (PDOException $error) {
            Response::sendError($error);             
        }
    } 

    /**
     * Get the list of all availabile courses that are both
     * created by the logged user and created by other users
     */
    public static function list(){
        try {
            $data = CourseService::getAll();
            $message = "Query Success";

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error) {
            Response::sendError($error);
        } 
    }

    /**
     * Searches for a list of courses based on a given name
     *
     * Required fields:
     *   - name - name of appointment to be searched
     */
    public static function search(){
        RequestMiddleware::requireFields(["name"]);

        $data = Request::getBody();

        try {

            $result = CourseService::searchByName($data["name"]);
            $message = "Query Success";

            Response::sendJson(200, true, $message, $result);

        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }

    /**
     * deletes a course that is created by the logged user
     * - throws 400 error status if course is not found
     *
     * Required fields:
     *   - id - the id of course from courses table
     */
    public static function delete(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["id"]);

        $user = Cookie::getUser();

        $data = Request::getBody();
        $data["created_by"] = $user->id;

        try {
            $affectedRows = CourseService::deleteById($data);

            if ($affectedRows <= 0){
                $message = "Course not found";
                Response::sendJson(400, false, $message, null);
            }

            $message = "Course deleted successfully";
            $data = [ "affected_rows" => $affectedRows ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Searches for a list of course that is belong to 
     * a certain user
     *
     * Required fields:
     *  user_id - id of user that you need to search for courses
     */
    public static function findUser(){
        RequestMiddleware::requireFields(['user_id']);

        try {
            $param = Request::getBody();

            $result = CourseService::getUserCourseList($param);
            $message = "Search Operation Success";

            Response::sendJson(200, true, $message, $result);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Returns a list of courses that is created by the logged
     * user, this will be used to retrieve the courses that
     * you make, in which the creator (you) will also have
     * the permission to delete it
     */
    public static function selfList(){
        AuthMiddleware::requireAuth();

        $user_id = Cookie::getUser()->id;

        try {
            $list = CourseService::getAllCreated($user_id);
            $message = "Searched Successfully";

            Response::sendJson(200, true, $message, $list);

        } catch (PDOException $error){
            Response::sendError($error); 
        } 
    }

    /**
     * Get the courses assigned to the logged user
     */
    public static function getAssigned(){
        AuthMiddleware::requireAuth();

        $user_id = Cookie::getUser()->id;

        try {
            $param = ["user_id" => $user_id];

            $result = CourseService::getAssigned($param);
            $message = "Search success";

            Response::sendJson(200, true, $message, $result);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Allows users to remove the courses that they are enrolled/teaching
     *
     * Required fields:
     *      - course_id - the id of course to be removed
     */
    public function unuse(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["course_id"]);

        $user_id = Cookie::getUser()->id;

        $body = Request::getBody();
        $body["user_id"] = $user_id;

        try {
            $affectedRows = CourseService::unenrollUser($body);


            if ($affectedRows >= 1){
                $data = [ "affected_rows" => $affectedRows ];
                $message = "Deleted successfully";

                Response::sendJson(200, true, $message, $data);
            }
            
            $message = "Course not found";

            Response::sendJson(400, false, $message, null);

        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }
}
