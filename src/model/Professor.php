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
}
