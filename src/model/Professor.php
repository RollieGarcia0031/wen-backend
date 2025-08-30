<?php

require_once __DIR__ . '/AppModel.php';

class Professor extends AppModel {
    public function isVerified(){
        $id = $_SESSION['uid'];
        $stment = $this->db->prepare("SELECT role from users WHERE id = ?");
        $stment->execute([$id]);
        $role = $stment->fetch(PDO::FETCH_ASSOC)['role'];

        return $role === 'professor';
    }

    public function addProfile(int $year, string $department, int $id) {
        if (!$this->isVerified()){
            $this->code = 401;
            $this->message = "User is not a professor";
            return false;
        }

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
        if (!$this->isVerified()){
            $this->code = 401;
            $this->message = "User is not a professor";
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

    public function getAvailability($id){
        $query = "SELECT * FROM availability WHERE user_id = ?";
        $stment = $this->db->prepare($query);
        $stment->execute([$id]);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result){
            $this->code = 500;
            $this->message = "Error getting availability";
            return false;
        }
        $this->code = 200;
        $this->data = $result;
        $this->message = "Availability fetched";
        return true;
    }
}
