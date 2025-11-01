<?php

namespace App\Service;

use App\Database\Database;
use SQLite3;

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

    /**
     *  Searches for a list of all courses that belong to a certain
     *  user
     *
     *  @param array $param {
     *      @type string $user_id   target user id
     *  }
     */
    public static function getUserCourseList(array $param):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                uc.id,
                uc.year,
                c.name,
                c.description
            FROM user_class uc
            LEFT JOIN courses c
                ON uc.course_id = c.id
            WHERE uc.user_id = :user_id
            ORDER BY uc.year ASC
        SQL; 

        $stment = $conn->prepare($q);

        $stment->execute($param);
        $result = $stment->fetchAll();

        return $result;
    }

    /**
     * Retrievese a list of courses filtered by the created_by
     * field in the table
     *
     * @param string $created_by - the id of the user who created courses 
     */
    public static function getAllCreated(string $created_by): array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT 
                id,
                name,
                description
            FROM courses
            WHERE created_by = ?
        SQL;

        $stment = $conn->prepare($q); 
        $stment->execute([$created_by]);

        $result = $stment->fetchAll();

        return $result;
    }

    /**
     * Removes a course that is assigned to a user
     * @param array $params {
     *      @type string user_id
     *      @type int course_id
     * }
     */
    public static function unenrollUser(array $param): int
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            DELETE FROM user_class
            WHERE
                id = :course_id
                AND
                user_id = :user_id 
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($param);

        $affectedRow = $stment->rowCount();

        return $affectedRow;
    }

    /**
     * Searches for a list of courses in the user_courses table
     */
    public static function getAssigned(array $param):array
    {
        $conn = Database::get()->connect();
        $stment = $conn->prepare(<<<SQL
            SELECT
                uc.id,
                uc.year,
                c.name,
                c.description
            FROM user_class uc
            LEFT JOIN courses c
                ON c.id = uc.course_id
            WHERE uc.user_id = :user_id
            ORDER BY c.name ASC;
        SQL);
    
        
        $stment->execute($param);
        $result = $stment->fetchAll();

        return $result;
    }
}
