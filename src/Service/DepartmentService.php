<?php

namespace App\Service;

use App\Database\Database;

class DepartmentService {

    /**
     * Add a user to a department
     * 
     * @param int $user_id - user id of professor
     * @param int $department_id - id of department
     */
    public static function addUserToDepartment($user_id, $department_id): void
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            INSERT INTO professor_departments
                (user_id, department_id)
            VALUES
                (?, ?);
        SQL;

        $stmt = $conn->prepare($q);
        $stmt->execute([$user_id, $department_id]);
    }
}