<?php

declare(strict_types=1);

namespace SwiftShip;

class Auth
{
    private const SESSION_KEY = 'swiftship_admin';

    private string $username;
    private string $passwordHash;

    public function __construct(array $config)
    {
        $this->username = $config['username'];
        $this->passwordHash = $config['password_hash'];

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function attempt(string $username, string $password): bool
    {
        if ($username !== $this->username) {
            return false;
        }

        if (!password_verify($password, $this->passwordHash)) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = $this->username;

        return true;
    }

    public function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY] === $this->username;
    }

    public function requireAuth(): void
    {
        if (!$this->check()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }
}


