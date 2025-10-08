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
    public static function assign(array $data): int
    {
        $conn = Database::get()->connect();

        $q = "INSERT INTO user_class
            (user_id, course_id, year)
            VALUES
                (:user_id, :course_id, :year)
        ";

        $stment = $conn->prepare($q);

        $stment->execute($data);

        return  $conn->lastInsertId();
    }


    /**
     * Searches for a list of all courses available
     */
    public static function getAll(): array
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare("
            SELECT 
                c.*,
                u.name as created_by
            FROM courses c
            LEFT JOIN users u ON u.id = c.created_by
            ORDER BY c.name ASC
        ");
        $stment->execute();

        return $stment->fetchAll();
    }

    /**
     * Searches for a list of courses filtered by name
     */
    public static function searchByName(string $name): array
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare("
            SELECT 
                c.*,
                u.name as created_by
            FROM courses c
            LEFT JOIN users u ON u.id = c.created_by
            WHERE c.name ~* :name
            ORDER BY c.name ASC
            ");

        $stment->execute(["name"=>$name]);
        
        $result = $stment->fetchAll();
        return $result;
    }

    /**
     *  Deletes a course from the database, filtered
     *  by the id, and created_by fields
     *
     *  @return int - The count of affected rows
     */
    public static function deleteById(array $data): int{
        $conn = Database::get()->connect();

        $stment = $conn->prepare("
            DELETE FROM courses
            WHERE
                (id = :id AND created_by = :created_by)
        ");
        
        $stment->execute($data);

        $count = $stment->rowCount();

        return $count;
    }
}
