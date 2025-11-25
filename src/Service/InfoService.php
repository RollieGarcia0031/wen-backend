<?php

namespace App\Service;

use App\Database\Database;

class InfoService {

    /**
     * Update a student info in database
     *
     * @param array $params {
     *      @type string first_name
     *      @type string last_name
     *      @type string middle_name
     *      @type string birthday
     *      @type string gender
     * }
     *
     * @param string $user_id - user of student
     */
    public static function updateStudent(array $params, string $user_id): void
    {
        $conn = Database::get()->connect();

        // Filter out params not allowed in the update query (security)
        $allowed = [
            'first_name',
            'last_name',
            'middle_name',
            'birthday',
            'gender'
        ];

        $fields = [];
        $bindings = [];

        foreach ($params as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "`$key` = :$key";   // build SET field
                $bindings[":$key"] = $value;     // bind value
            }
        }

        // If no valid fields, do nothing
        if (empty($fields)) {
            return;
        }

        // Build the final SQL dynamically
        $q = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = :user_id";

        $stmt = $conn->prepare($q);

        // Bind parameters
        foreach ($bindings as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->bindValue(":user_id", $user_id);

        $stmt->execute();
    }

}