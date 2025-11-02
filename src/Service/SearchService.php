<?php

namespace App\Service;

use App\Database\Database;
use App\Util\JSON_MAKER;
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
                u.id,
                JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'year', uc.year,
                        'class', c.name
                    )::json
                ) AS classes
            FROM users u

            JOIN user_class uc
                ON uc.user_id = u.id

            JOIN courses c
                ON uc.course_id = c.id

            WHERE 
                u.name ~* :user_name
                AND u.role = 'professor'

            GROUP BY u.name, u.id
            ORDER BY u.name ASC;

        SQL; 

        $stment = $conn->prepare($q);
        $stment->execute($params);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);

        // convert the nested attribute (classes) to a
        // format easy to parse as json
        foreach ($result as &$row){
            $classes = json_decode($row['classes']);

            $row['classes'] = $classes;
        }

        return $result;
    }
}
