<?php

namespace App\Service;

use App\Database\Database;

class NotificationService {
    
    /**
     * Counts the notification of user with a status 0
     *
     * @param array $params {
     *      @type string $user_id
     * }
     */
    public static function countUnread(array $params){
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT count(*)
            FROM user_notifications
            WHERE (
                status = 0
                AND user_id = :user_id
            );
        SQL;

        $stment = $conn->prepare($q);

        $stment->execute($params);
        $result = $stment->fetch();

        return $result;
    }
}
