<?php
namespace App\Service;

use App\Database\Database;

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

}