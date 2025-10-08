<?php

namespace App\Service;

use App\Database\Database;

class CourseService {

    /**
     *
     * Assigns a user to a specific class,
     * this can be used by professors or students
     * in order to belong to a specific course
     * and assign their current year
     *
     */    
    public static function assign(array $data): int{
        $conn = Database::get()->connect();

        $q = "INSERT INTO user_class
            (user_id, course_id, year)
            VALUES
                (:user_id, :course_id, :year)
        ";

        $stment = $conn->prepare($q);

        $stment->execute([$data]);

        return  $conn->lastInsertId();
    }
    
}
