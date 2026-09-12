<?php

class User
{
    public function __construct(private PDO $database) {}

    public function create(string $name, string $username, string $email, string $password, string $role = 'customer'): bool
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (name, username, email, password, role) VALUES (:name, :username, :email, :password, :role)'
        );

        return $statement->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    public function findByLogin(string $login): ?array
    {
        $statement = $this->database->prepare("SELECT * FROM users WHERE (email = :email OR username = :username) AND status = 'active' LIMIT 1");
        $statement->execute(['email' => $login, 'username' => $login]);

        $user = $statement->fetch();
        return $user ?: null;
    }
}
