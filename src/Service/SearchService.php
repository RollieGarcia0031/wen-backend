<?php

namespace App\Service;

use App\Database\Database;
use PDO;

class SearchService {

    /**
     * Search for a list of professor filtered by user name
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
                u.id
            FROM users u
            WHERE 
                u.name ~* :user_name
                AND u.role = 'professor'
            GROUP BY u.name, u.id
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
                ) AS availabilities
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

        // convert nested json in parse-ready json
        foreach ($result as &$row){
            $av = $row['availabilities'];
            $row['availabilities'] = json_decode($av, true);

            $classes = $row['classes'];
            $row['classes'] = json_decode($classes, true);
        }

        return $result;
    }
}
