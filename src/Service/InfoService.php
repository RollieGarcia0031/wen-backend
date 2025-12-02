<?php

namespace App\Service;

use App\Database\Database;
use Error;
use Exception;
use PDO;

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

    /**
     * Update a professor info in database
     * 
     * @param array $params {
     *      @type string? first_name
     *      @type string? last_name
     *      @type string? middle_name
     *      @type string? birthday
     *      @type string? bio
     *      @type string? gender
     *      @type string? cellphone_number
     * }
     */
    public static function updateProfessor(array $params, string $user_id):void
    {
        $conn = Database::get()->connect();

        // Ensure only valid columns are processed
        $allowedColumns = [
            'first_name',
            'last_name',
            'middle_name',
            'birthday',
            'bio',
            'gender',
            'cellphone_number'
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
            INSERT INTO professor_info (" . implode(', ', $columns) . ")
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


    public static function getProfessor($user_id): array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                pi.*,
                u.name As user_name,
                u.email,
                d.name AS department_name,
                JSONB_AGG(
                    JSONB_BUILD_OBJECT(
                        'section_code', s.section_code,
                        'year_level', s.year_level,
                        'course_code', c.course_code,
                        'course_name', c.course_name
                    )
                ) FILTER (WHERE s.section_id IS NOT NULL) AS sections
            FROM professor_info pi
            JOIN users u ON u.id = pi.user_id
            LEFT JOIN professor_departments pd
                ON pd.user_id = u.id
            LEFT JOIN departments d
                ON d.id = pd.department_id
            LEFT JOIN professor_sections ps
                ON ps.user_id = u.id
            LEFT JOIN sections s
                ON s.section_id = ps.section_id
            LEFT JOIN courses c
                ON c.course_id = s.course_id
            WHERE pi.user_id = :user_id
            GROUP BY
                pi.user_id,
                u.name,
                u.email,
                d.name,
                d.code
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute(['user_id' => $user_id]);
        $result = $stment->fetch();

        if (isset($result['sections'])) {
            $result['sections'] = json_decode($result['sections'], true);
        }

        
        if (!$result) {
            throw new Exception("Professor not found", 404);
        }
        return $result;
    }

    /**
     * Get a full info of a target student
     * 
     * @param string $user_id - id of the target student
     */
    public static function getStudent($user_id): array
    {
        $conn = Database::get()->connect();
        
        $q = <<<SQL
            SELECT
                si.*,
                u.name As user_name,
                u.email,
                JSONB_AGG(
                    JSONB_BUILD_OBJECT(
                        'section_code', s.section_code,
                        'year_level', s.year_level,
                        'course_code', c.course_code,
                        'course_name', c.course_name
                    )
                ) FILTER (WHERE s.section_id IS NOT NULL) AS sections
            FROM student_info si
            JOIN users u ON u.id = si.user_id
            LEFT JOIN student_sections ss
                ON ss.user_id = u.id
            LEFT JOIN sections s
                ON s.section_id = ss.section_id
            LEFT JOIN courses c
                ON c.course_id = s.course_id
            WHERE si.user_id = :user_id
            GROUP BY
                si.user_id,
                u.name,
                u.email
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute(['user_id' => $user_id]);
        $result = $stment->fetch();

        if (isset($result['sections'])) {
            $result['sections'] = json_decode($result['sections'], true);
        }

        if (!$result) {
            throw new Exception("Student not found", 404);
        }
        return $result;
    }
}