<?php

namespace App\Controller;

use App\Base\Controller;
use App\Model\Course;
use App\Http\Request;
use App\Http\Response;
use PDOException;

class CourseController extends Controller {
    public function create(){
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
}
