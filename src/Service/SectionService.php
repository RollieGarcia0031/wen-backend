<?php
namespace App\Service;

use App\Database\Database;
use PDOException;

class SectionService
{

    /**
     * Inserts a new row either in the student sections table
     * or in the professor sections table depending on the 
     * given role
     * 
     * @param array $params {
     *      @type int $user_id
     *      @type int $section_id
     * }
     * 
     * @param string $role - the role of the user { professor, student }
     */
    public static function enrollUser(array $params, string $role):void
    {
        $conn = Database::get()->connect();

        if ($role === 'professor') {

            $stmt = $conn->prepare(<<<SQL
                INSERT INTO professor_sections
                    (user_id, section_id)
                VALUES
                    (:user_id, :section_id)
            SQL);
            $stmt->execute($params);
            
        } else {

            $stmt = $conn->prepare(<<<SQL
                INSERT INTO student_sections
                    (user_id, section_id)
                VALUES
                    (:user_id, :section_id)
            SQL);
            $stmt->execute($params);

        }
    }

    /**
     * Retrieves the list of all available sections
     * 
     * @return array - the list of all sections to be enrolled
     */
    public static function getAll():array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
        SELECT
            c.course_name,
            c.course_code,
            c.course_id,
            JSON_AGG(
                JSON_BUILD_OBJECT(
                    'section_id', s.section_id,
                    'year_level', s.year_level,
                    'section_code', s.section_code
                )
                ORDER BY s.year_level, s.section_code
            ) AS sections
        FROM courses c
        LEFT JOIN sections s
            ON s.course_id = c.course_id
        GROUP BY
            c.course_id, c.course_name, c.course_code
        ORDER BY
            c.course_name
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute();
        $result = $stment->fetchAll();

        foreach ($result as &$row){
            $row['sections'] = json_decode($row['sections'], true);
        }
        return $result;
    }

    /**
     * Unenroll the logged user from a specific section
     * 
     * @param array $params {
     *      @type int $user_id
     *      @type int $section_id
     * }
     */
    public static function unenrollUser(array $params, string $role):void
    {
        $conn = Database::get()->connect();

        if ($role === 'professor') {

            $stmt = $conn->prepare(<<<SQL
                DELETE FROM professor_sections
                WHERE
                    user_id = :user_id
                    AND section_id = :section_id
            SQL);
            $stmt->execute($params);

        } else {

            $stmt = $conn->prepare(<<<SQL
                DELETE FROM student_sections
                WHERE
                    user_id = :user_id
                    AND section_id = :section_id
            SQL);
            $stmt->execute($params);

        }
    }

    /**
     * Retrieves the list of sections owned by the logged user
     * 
     * @param string $user_id
     * @param string $role - the role of the user { professor, student }
     * 
     * @return array - the list of owned sections
     */
    public static function getOwned(string $user_id, string $role): array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                c.course_name,
                c.course_code,
                JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'section_id', s.section_id,
                        'year_level', s.year_level,
                        'section_code', s.section_code
                    )
                    ORDER BY s.year_level, s.section_code
                ) AS sections
            FROM courses c
            LEFT JOIN sections s
                ON s.course_id = c.course_id
        SQL;

        $params = [];   // <-- parameter holder

        // Conditional JOIN + WHERE
        if ($role === 'professor') {
            $q .= <<<SQL
                LEFT JOIN professor_sections ps
                    ON ps.section_id = s.section_id
                WHERE ps.user_id = :user_id
            SQL;
            $params['user_id'] = $user_id;

        } elseif ($role === 'student') {
            $q .= <<<SQL
                LEFT JOIN student_sections ss
                    ON ss.section_id = s.section_id
                WHERE ss.user_id = :user_id
            SQL;
            $params['user_id'] = $user_id;
        }

        $q .= <<<SQL
            GROUP BY
                c.course_id, c.course_name, c.course_code
            ORDER BY
                c.course_name
        SQL;

        $stment = $conn->prepare($q);

        // execute with parameters ONLY if needed
        $stment->execute($params);

        $result = $stment->fetchAll();

        foreach ($result as &$row) {
            $row['sections'] = json_decode($row['sections'], true);
        }

        return $result;
    }

    /**
     * Allows a user to add a multiple set of sections to their account
     * 
     * @param array $params {
     *      @type int $user_id - the id of the user to add sections to
     *      @type array $section_ids - the list of sections to add
     * }
     * @param string $role - the role of the user { professor, student } 
     */
    public static function enrollMultiple(array $params, string $role):void
    {
        $conn = Database::get()->connect();
        
        $section_ids = $params['section_ids'];
        $user_id = $params['user_id'];

        try {
            $conn->beginTransaction();
    
            if ($role === 'professor') {            
                foreach($section_ids as $section_id) {
                    $stmt = $conn->prepare(<<<SQL
                        INSERT INTO professor_sections
                            (user_id, section_id)
                        VALUES
                            (?, ?)
                    SQL);
                    $stmt->execute([$user_id, $section_id]);
                }
            } else {
                foreach($section_ids as $section_id) {
                    $stmt = $conn->prepare(<<<SQL
                        INSERT INTO student_sections
                            (user_id, section_id)
                        VALUES
                            (?, ?)
                    SQL);
                    $stmt->execute([$user_id, $section_id]);
                }
            }

            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}