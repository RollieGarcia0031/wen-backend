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

}