<?php

require_once __DIR__ . '/AppModel.php';

class Professor extends AppModel {
    public function addProfile(int $year, string $department, int $id) {
        $sql = "INSERT INTO professors (user_id, year, department) VALUES (?, ?, ?)";

        $stment = $this->db->prepare($sql);
        $stment->execute([$id, $year, $department]);

        if (!$stment) {
            $this->code = 500;
            $this->message = "Error adding profile";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Profile added successfully";
        $this->data = ['id' => $this->db->lastInsertId()];
        return true;
    }

    public function addAvailability($userId, $day, $start, $end){
        $stment = $this->db->prepare("SELECT role from users WHERE id = ?");
        $stment->execute([$userId]);
        $role = $stment->fetch(PDO::FETCH_ASSOC)['role'];
        if($role !== 'professor') {
            $this->code = 403;
            $this->message = "You must be a professor to add availability";
            return false;
        }

        $query = "INSERT INTO availability (user_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stment = $this->db->prepare($query);

        $stment->execute([$userId, $day, $start, $end]);

        if (!$stment) {
            $this->code = 500;
            $this->message = "Error adding availability";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Availability added successfully";
        $this->data = ['id' => $this->db->lastInsertId()];
        return true;
    }
}
