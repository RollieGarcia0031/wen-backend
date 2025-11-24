<?php

namespace App\Service;

use App\Database\Database;
use PDOException;

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

    /**
     * Get all departments
     */
    public static function getAllDepartments():array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT * FROM departments;
        SQL;

        return $conn->query($q)->fetchAll();
    }

    /**
     * Get all departments owned by a user
     * 
     * @param string $user_id - user id of professor
     */
    public static function getOwnedDepartments(string $user_id):array
    {
        $conn = Database::get()->connect();

        $q  = <<<SQL
            SELECT
                d.*
            FROM professor_departments pd
            JOIN departments d
                ON pd.department_id = d.id
            WHERE pd.user_id = ?
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute([$user_id]);
        return $stment->fetchAll();
    }


    /**
     * Remove a user from a department
     * 
     * @param string $user_id - user id of professor
     * @param int $department_id - id of department
     */
    public static function removeUserFromDepartment(string $user_id, int $department_id):void
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            DELETE FROM professor_departments
            WHERE user_id = ? AND department_id = ?;
        SQL;

        $stmt = $conn->prepare($q);
        $stmt->execute([$user_id, $department_id]);
    }

    /**
     * Add a user to multiple departments
     */
    public static function addUserToDepartments(string $user_id, array $department_ids):void
    {
        $conn = Database::get()->connect();

        try {
            $conn->beginTransaction();

            foreach ($department_ids as $department_id) {
                self::addUserToDepartment($user_id, $department_id);
            }

            $conn->commit();
        } catch (PDOException $error) {
            $conn->rollBack();
            throw $error;
        }
    }
}