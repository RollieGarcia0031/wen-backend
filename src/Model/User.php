<?php

namespace App\Model;

use App\Database\Database;
use PDO;

class User
{
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        ?string $role = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public static function create(array $data): User
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $q = "INSERT INTO users
            (name, email, password, role)
            VALUES (:name, :email, :password, :role)
        ";

        $stment = Database::get()->connect()->prepare($q);
        $stment->execute($data);
        $id = Database::get()->connect()->lastInsertId();

        return new User(
            $id,
            $data['name'],
            $data['email'],
            "***", 
            $data['role']
        );
    }

    public static function getByEmail(string $email): User
    {
        $q = 'SELECT * FROM users WHERE email = :email';
        $stment = Database::get()->connect()->prepare($q);

        $stment->execute(['email' => $email]);
        
        $user = $stment->fetchAll(PDO::FETCH_OBJ)[0];

        return new User(
            $user->id,
            $user->name,
            $user->email,
            $user->password,
            $user->role
        );
    }
}
