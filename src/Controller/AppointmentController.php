<?php

namespace App\Controller;

use App\Http\Cookie;
use App\Http\Response;
use App\Http\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\UserMiddleware;
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
     *  - availability_id - primary key of availability
     *  - message         - string message to be sent as "topic" or header
     *  - target_date     - date to be assigned
     */
    public function send(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole('student');
        RequestMiddleware::requireFields([
            'availability_id',
            'message',
            'target_date'
        ]);

        $userId = Cookie::getUser()->id;

        $params = Request::getBody();
        $params['student_user_id'] = $userId;

        try {
            $newId = AppointmentService::sendAppointment($params);

            $message = "Appointment sent successfully";
            $data = [ "id" => $newId ];

            Response::sendJson(200, true, $message, $data);

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

        $data = null;
        $message = null;
        
        try {

            if ($userRole == 'student'){
                $params['student_user_id'] = $user_id;

                $message = "Appointment retrieved";
                $data = AppointmentService::getAllSentAppointments($params);
                
            } else if ($userRole == 'professor'){
                $params['professor_user_id'] = $user_id;

                $message = "Appointment retrieved";
                $data = AppointmentService::getAllRecievedAppointments($params);
            }

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Updates an appointment status from pending to "confirmed"
     * - Only allowed for professor
     *
     * Required fileds:
     * - id - primary id of the appointment to be accepted
     */
    public function accept(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("professor");
        RequestMiddleware::requireFields(["id"]);

        $params = Request::getBody();

        $params['professor_user_id'] = Cookie::getUser()->id;
        $params['status'] = 1;

        try {

            $affectedRows = AppointmentService::approveAppointment($params);

            if ($affectedRows == 0){
                $message = "No appointment updated";
                Response::sendJson(400, false, $message, null);
            }

            $message = "Update Success";
            $data = [ "affected_rows" => $affectedRows ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        } catch (Exception $error){
            Response::sendError($error);
        }
    }

    /**
     * Deletes an appointment
     * - Only allowed for students who originally created the appointment
     *
     * Required fields:
     *  - id - primary id of appointment to be deleted
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

            $message = "Delete Success";
            $data = [ "affected_rows" => $affectedRows ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        } catch (Exception $error){
            Response::sendError($error);
        }
    }

    /**
     * Updates the message of an appointment
     * - Only allowed for students who originally created the appointment
     *
     */
    public static function updateMessage(){
        AuthMiddleware::requireAuth();
        UserMiddleware::requireRole("student");
        RequestMiddleware::requireFields(["id", "message"]);

        $params = Request::getBody();

        try {
            $params['student_user_id'] = Cookie::getUser()->id;

            $affectedRows = AppointmentService::updateMessage($params);

            $message = "Update Succes";
            $data = [ "affected_rows" => $affectedRows ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        }
    }

    /**
     * Declines an appointment
     * - Only allowed for professors who received the appointment
     * - Only works for pending (status 0) appointments
     *
     * - Required fields:
     *   - id - id of the appointment from appointments table
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

                $message = "No appointment updated";

                Response::sendJson(400, false, $message, null);
            }

            $message = "Appointment has been declined";
            $data = [ "affected_rows" => $affectedRows ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error){
            Response::sendError($error);
        } catch (Exception $error){
            Response::sendError($error);
        }
    }

    /**
     * Allows professor to hide multiple appointments
     *
     * Required fields:
     *  - ids - array of ids of appointment to be deleted by the professor
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
                $message = "Appointments you tried to hide does not exist";

                Response::sendJson(400, false, $message, null);
            }

            $message = "Appointments has been hidden successfully";
            $data = [ "affected_by" => $affectedRows ];

            Response::sendJson(200, true, $message, $data);

        } catch (PDOException $error) {
            Response::sendError($error);
        }

    }

    /**
     * Retrieve all of both pending and approved appointment
     * for the current day
     *
     * Required field:
     *  - cursor_id - the id of the appointment serving as next index,
     *                if unknown, 0 (zero) can be passed as value,
     *                but for further requests, next_cursor from previous
     *                response shall be used as the current cursor_id
     *                in the new request for pagination to work
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

            $message = "Appointments successfully retrieved";

            Response::sendJson(200, true, $message, $result);

        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }

    /**
     * Returns the count of pending, approved, and declined appointments
     * that exist in a certain time_range
     *
     * Required field:
     *  - time_range - { 'today' | 'tommorow' | 'current_week' }
     */
    public function count(){
        AuthMiddleware::requireAuth();
        RequestMiddleware::requireFields(["time_range"]);

        $param = Request::getBody();
        $user_id = Cookie::getUser()->id;

        $time_range = $param['time_range'];

        $newParam = [ "user_id" => $user_id ];
        $result = null;

        try {
            switch ($time_range){

                case 'today':
                    $result = AppointmentService::getStudentsAppointmentCountToday($newParam);
                    break;

                case 'tommorow':
                    $result;
                    break;

                case 'this_week':
                    $result;
                    break;
            }

            $message = "Appointments counted";

            Response::sendJson(200, true, $message, $result);

        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }
}
