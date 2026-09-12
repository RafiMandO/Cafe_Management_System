<?php

class User
{
    public function __construct(private PDO $database) {}

    public function create(string $name, string $email, string $password): bool
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );

        return $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        return $user ?: null;
    }
}
