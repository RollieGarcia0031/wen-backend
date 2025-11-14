<?php

namespace App\Model;

use App\Database\Database;

class Course {
    public int $id;
    public int $created_by;
    public string $name;
    public string $description;

    public function __construct(
        int $id,
        int $created_by,
        string $name,
        string $description
    )
    {
        $this->id = $id;
        $this->created_by = $created_by;
        $this->name = $name;
        $this->description = $description; 
    }

    /**
     * Inserts a new course into the courses table
     *
     * @param array $data {
     *     @type string name - name of course
     *     @type string description
     *     @type string created_by - user id of course owner
     * }
     */
    public static function create(array $data): Course
    {
        $q = "INSERT INTO courses
            (created_by, name, description)
            VALUES 
                (:created_by, :name, :description)";

        $stment = Database::get()->connect()->prepare($q);
        $stment->execute($data);
        $id = Database::get()->connect()->lastInsertId();

        return new Course(
            $id,
            $data['created_by'],
            $data['name'],
            $data['description']
        );
    }
}
