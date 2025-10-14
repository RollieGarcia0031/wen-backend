<?php

namespace App\Service;

use App\Database\Database;

class AvailabilityService {
    /**
     * Creates a new availability and saves it in the 
     * database, this allows professors to give options
     * to students on when can they only recieve appointment
     *
     * @param $user_id id of logged professor
     * @param $time_start 00:00 24 hour format time
     * @param $time_end 00:00 24 four format time
     * @param $day the day of week to be assigned
     */
    public static function create(
        int $user_id,
        string $time_start,
        string $time_end,
        string $day 
    ): int
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare("
            INSERT INTO availability
            (user_id, day_of_week, start_time, end_time)
            VALUES (?, ?, ?, ?) 
        ");

        $stment->execute([
            $user_id, $day, $time_start, $time_end
        ]);
    
        $lastId = $conn->lastInsertId();

        return $lastId;
    }

    /**
     * Searches a list of availability based on a given user_id
     *
     * @param array $param {
     *      @type string $user_id the target user id
     * }
     */
    public static function getByUser(array $param):array
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare("
            SELECT * FROM availability
            WHERE user_id = :user_id
            ORDER BY day_of_week ASC
        ");

        $stment->execute($param);

        $result = $stment->fetchAll();
        return $result;
    }

    /**
     *  Deletes an availability based on id
     *  and user_id
     *
     *  @param array @param {
     *      @type int $user_id id of availability owner
     *      @type int $id      unique id of the availability
     *  }
     */
    public static function deleteById(array $param):int
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare("
            DELETE FROM availability
            WHERE (
                user_id = :user_id
                AND
                id = :id
            )
        "); 

        $stment->execute($param);

        $rowCount = $stment->rowCount();
        return $rowCount;
    }
}
