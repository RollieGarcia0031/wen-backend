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

    /**
     * Lists all of the unread notifications for the user
     * @param array $params {
     *      @type string $user_id
     *      @type int    $end_from - id of the last notification received
     * }
     */
    public static function listUnread(array $params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT *
            FROM user_notifications un
            JOIN notifications n
                ON un.notification_id = n.id
            WHERE (
                un.id > :end_from
                AND un.status = 0
                AND un.user_id = :user_id
            )
            ORDER BY n.created_at DESC
            LIMIT 10
        SQL;

        $stment = $conn->prepare($q);

        $lastId = intval($params["end_from"]);

        if( $lastId > 0 )
            $params["end_from"] = $lastId + 1;

        $stment->execute($params);
        $results = $stment->fetchAll();

        return $results;
    }
}