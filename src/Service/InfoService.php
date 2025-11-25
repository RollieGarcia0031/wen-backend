<?php

namespace App\Service;

use App\Database\Database;

class InfoService {

    /**
     * Update a student info in database
     *
     * @param array $params {
     *      @type string? first_name
     *      @type string? last_name
     *      @type string? middle_name
     *      @type string? birthday
     * }
     *
     * @param string $user_id - user of student
     */
    public static function updateStudent(array $params, string $user_id): void
    {
        $conn = Database::get()->connect();

        // Ensure only valid columns are processed
        $allowedColumns = [
            'first_name',
            'last_name',
            'middle_name',
            'birthday'
        ];

        $fields = array_intersect_key($params, array_flip($allowedColumns));

        if (empty($fields)) {
            return; // Nothing to update
        }

        // Always include user_id for INSERT
        $fields['user_id'] = $user_id;

        // Build dynamic column list and placeholders for INSERT
        $columns = array_keys($fields);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        // Build dynamic UPDATE clause only for fields except user_id
        $updateSets = [];

        foreach ($fields as $column => $value) {
            if ($column === 'user_id') {
                continue;
            }
            $updateSets[] = "$column = EXCLUDED.$column";
        }

        $sql = "
            INSERT INTO student_info (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")
            ON CONFLICT (user_id)
            DO UPDATE SET " . implode(', ', $updateSets) . ";
        ";

        $stmt = $conn->prepare($sql);

        // Bind values dynamically
        foreach ($fields as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

}