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
    public static function countUnread(array $params):array
    {
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

    /**
     * Updates all row of table user_notification from status
     * 0 (zero) to status 1 (one)
     */
    public static function markAllAsRead($params):void
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            UPDATE user_notifications
            SET status = 1
            WHERE (
                status = 0
                AND user_id = :user_id
            )
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($params);
    }
}
