<?php

namespace App\Service;

use App\Database\Database;
use PDO;

class SearchService {

    /**
     * Search for a list of professor filtered by user name
     * 
     * Only professor with existing professor detail, and department will show up in search result
     *
     * @param array $params {
     *      @type string $user_name User name to be searched
     * }
     */
    public static function searchProfessorByName(array $params){
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT 
                u.name,
                u.id,
                d.name AS department_name
            FROM users u
            LEFT JOIN professor_info pi
                ON pi.user_id = u.id
            LEFT JOIN professor_departments pd
                ON pd.user_id = u.id
            LEFT JOIN departments d 
                ON d.id = pd.department_id
            WHERE 
                u.name ~* :user_name
                AND u.role = 'professor'
                AND pi.user_id IS NOT NULL
                AND pd.user_id IS NOT NULL
            ORDER BY u.name ASC;

        SQL; 

        $stment = $conn->prepare($q);
        $stment->execute($params);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Search for availability and classes of a user
     *
     * @param array $params {
     *      @type string $professor_user_id uid of the target professor
     * }
     *
     * @return array the info about professor
     */
    public static function searchUserInfo($params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                u.name,
                u.email,
                u.id,
                (SELECT
                    JSONB_AGG(
                        DISTINCT JSONB_BUILD_OBJECT(
                            'start_time', av.start_time,
                            'end_time', av.end_time,
                            'day_of_week', av.day_of_week,
                            'availability_id', av.id
                        )
                    )
                    FROM availability av
                    WHERE av.user_id = u.id
                ) AS availabilities,
                (SELECT
                    JSONB_AGG(
                        DISTINCT JSONB_BUILD_OBJECT(
                            'section_code', s.section_code,
                            'year_level', s.year_level,
                            'section_id', s.section_id,
                            'course_name', c.course_name,
                            'course_code', c.course_code
                        )
                    )
                    FROM professor_sections ps
                    LEFT JOIN sections s
                        ON ps.section_id = s.section_id
                    LEFT JOIN courses c
                        ON s.course_id = c.course_id
                    WHERE ps.user_id = u.id
                ) AS sections,
                (SELECT
                    JSON_AGG(
                        DISTINCT JSONB_BUILD_OBJECT(
                            'department_name', d.name,
                            'department_code', d.code
                        )
                    )
                    FROM professor_departments pd
                    LEFT JOIN departments d
                        ON pd.department_id = d.id
                    WHERE pd.user_id = u.id
                ) AS departments
            FROM users u
            WHERE (
                u.id = :professor_user_id
                AND role = 'professor'
            )
            GROUP BY u.id, u.name, u.email
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($params);

        $result = $stment->fetchAll();

        foreach ($result as &$row){
            $av = $row['availabilities'];
            $row['availabilities'] = json_decode($av, true);

            $sec = $row['sections'];
            $row['sections'] = json_decode($sec, true);

            $deo = $row['departments'];
            $row['departments'] = json_decode($deo, true);
        }

        return $result;
    }
}
