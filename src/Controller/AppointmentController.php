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
use Exception;

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
            $params['status'] = 1;

            $affectedRows = AppointmentService::approveAppointment($params);

            if ($affectedRows == 0){
                Response::sendJson(
                    400, false,
                    "No appointment updated",
                    ["affected_rows" => $affectedRows]
                );
            }

            Response::sendJson(
                200, true,
                "Update Success",
                ["affected_rows" => $affectedRows]
            );

        } catch (PDOException $error){
            Response::sendError($error);
        } catch (Exception $e){
            Response::sendError($e);
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
            $user = Cookie::getUser();
            $params['student_user_id'] = $user->id;

            $affectedRows = AppointmentService::delete($params, $user->name);

            Response::sendJson(
                200, true,
                "Delete Success",
                ["affected_rows" => $affectedRows]
            );

        } catch (PDOException $error){
            Response::sendError($error);
        } catch (Exception $error){
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

    /**
     * Declines an appointment
     * - Only allowed for professors who received the appointment
     * - Required fields:
     *   - id
     * - Sets status to 2 (declined)
     * - Only works for pending (status 0) appointments
     */
    public function decline(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        RequestMiddleware::requireFields(["id"]);

        $params = Request::getBody();
        $params['professor_user_id'] = Cookie::getUser()->id;

        try {
            $affectedRows = AppointmentService::declineAppointment($params);

            if ($affectedRows == 0){
                Response::sendJson(
                    400, false,
                    "No appointment updated",
                    ["affected_rows" => $affectedRows]
                );
            }

            Response::sendJson(
                200, true,
                "Update Success",
                ["affected_rows" => $affectedRows]
            );

        } catch (PDOException $error){
            Response::sendError($error);
        } catch (Exception $e){
            Response::sendError($e);
        }
    }

    /**
     * Allows professor to hide multiple appointments
     */
    public function hide(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        RequestMiddleware::requireFields(['ids']);

        $user_id = Cookie::getUser()->id;

        $params = Request::getBody();
        $params['professor_user_id'] = $user_id;

        try {
            $affectedRows = AppointmentService::hideMultiple($params);

            if ($affectedRows === 0) {
                Response::sendJson(400, false, 'No Rows affected', null);
            }

            Response::sendJson(200, true, "Query Success", [
                "affected_rows" => $affectedRows
            ]);
        } catch (PDOException $error) {
            Response::sendError($error);
        }
        
    }

    /**
     * Retrieve all of both pending and approved appointment
     * for the current day
     */
    public function currentDay(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(['cursor_id']);

        $user = Cookie::getUser();

        $params = Request::getBody();
        $params['user_id'] = $user->id;

        try {
            $result = null;

            if ($user->role === 'professor'){
                $result = AppointmentService::getCurrentRecivedAppointments($params);
            } else {
                $result = AppointmentService::getCurrentSentAppointments($params); 
            }

            Response::sendJson(200, true, "Query Success", $result);

        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }
}
