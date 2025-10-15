<?php

namespace App\Controller;

use App\Http\Cookie;
use App\Http\Response;
use App\Http\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use App\Model\Appointment;
use PDOException;

class AppointmentController {

    /**
     * Creates a new appointmentment
     * Creation is only allowed to logged students
     */ 
    public static function createNew(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("student");
        RequestMiddleware::requireFields([
            "availability_id",
            "message",
            "target_date"
        ]);

        $user_id = Cookie::getUser()->id;
        $param = Request::getBody();

        try {
            $appointment = new Appointment(
                $user_id,
                $param['availability_id'],
                "pending", 
                $param['message'],
                $param['target_date'] 
            );

            $appointment->create();

            $new_id = $appointment->id;

            if ($new_id >= 0){
                Response::sendJson(
                    201, true,
                    "Appointment Created",
                    ["new_id" => $new_id]
                );
            }

            Response::sendJson(
                300, false, "Creation Failed",
                null
            );

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }
}
