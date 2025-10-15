<?php

namespace App\Model;

use App\Database\Database;

class Appointment {

    public int    $id;
    public int    $student_user_id;
    public int    $availability_id;
    public string $status;
    public string $message;
    public string $target_date;

    public function __construct(
        int    $student_user_id,
        int    $availability_id,
        string $status,
        string $message,
        string $target_date
    )
    {
        $this->student_user_id = $student_user_id;
        $this->availability_id = $availability_id;
        $this->status = $status;
        $this->message = $message;
        $this->target_date = $target_date; 
    }

    public function create(){
        $conn = Database::get()->connect(); 

        $q = "INSERT INTO appointments (
            student_user_id,
            availability_id,
            status,
            message,
            target_date
        ) VALUES (?, ?, ?, ?, ?)";

        $stment = $conn->prepare($q);

        $stment->execute([
            $this->student_user_id,
            $this->availability_id,
            $this->status,
            $this->message,
            $this->target_date
        ]);

        $last_id = $conn->lastInsertId();

        $this->id = $last_id;
    }
}
