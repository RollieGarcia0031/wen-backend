<?php

namespace App\Controller;

use App\Http\Cookie;
use App\Http\Response;
use App\Http\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
use App\Model\Appointment;
use App\Service\AppointmentService;
use PDOException;

class AppointmentController {

    /**
     * Sends an appointment
     *
     * Only allowed for user logged with student role
     *
     * Required fields:
     *  - availability_id
     *  - message
     *  - target_date
     */
    public function send(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('student');
        RequestMiddleware::requireFields([
            'availability_id',
            'message',
            'target_date'
        ]);

        $params = Request::getBody();

        $userId = Cookie::getUser()->id;
        $params['student_user_id'] = $userId;

        try {
            $result = AppointmentService::sendAppointment($params);

            Response::sendJson(200, true, "Sent", ["id" => $result]);
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Returns the list of appointments of the logged user
     * - if opened by a student, it returns the sent appointments
     * - if opened by a professor, it returns the received appointments
     */
    public function getOwnList(){
        AuthMiddleware::requireAuth();

        $params = Request::getBody();

        $user_id = Cookie::getUser()->id;
        $userRole = Cookie::getUser()->role;

        
        try {
            $list = null;
            
            if ($userRole == 'student'){
                $params['student_user_id'] = $user_id;
                $list = AppointmentService::getAllSentAppointments($params);
                
            } else if ($userRole == 'professor'){
                $params['professor_user_id'] = $user_id;
                $list = AppointmentService::getAllRecievedAppointments($params);
            }

            Response::sendJson(200, true, "Query Success", $list);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Updates an appointment status from pending to "confirmed"
     * - Only allowed for professor
     */
    public function accept(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        RequestMiddleware::requireFields(["id"]);

        $params = Request::getBody();

        try {
            $params['professor_user_id'] = Cookie::getUser()->id;
            $params['status'] = "confirmed";

            $affectedRows = AppointmentService::updateStatus($params);

            Response::sendJson(
                200, true,
                "Update Success",
                ["affected_rows" => $affectedRows]
            );

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Deletes an appointment
     * - Only allowed for students who originally created the appointment
     */
    public static function delete(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("student");
        RequestMiddleware::requireFields(["id"]);

        $params = Request::getBody();

        try {
            $params['student_user_id'] = Cookie::getUser()->id;

            $affectedRows = AppointmentService::delete($params);

            Response::sendJson(
                200, true,
                "Delete Success",
                ["affected_rows" => $affectedRows]
            );

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Updates the message of an appointment
     * - Only allowed for students who originally created the appointment
     */
    public static function updateMessage(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("student");
        RequestMiddleware::requireFields(["id", "message"]);

        $params = Request::getBody();

        try {
            $params['student_user_id'] = Cookie::getUser()->id;

            $affectedRows = AppointmentService::updateMessage($params);

            Response::sendJson(
                200, true,
                "Update Success",
                ["affected_rows" => $affectedRows]
            );
        } catch (PDOException $error){
            Response::sendError($error);
        }
    }
}
