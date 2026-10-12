<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Usuario
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connection();
    }

    /** @return array<string, mixed>|null */
    public function buscarPorEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nombre, email, password_hash, rol, estado
             FROM usuarios
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $usuario = $statement->fetch();

        return $usuario === false ? null : $usuario;
    }

    public function crear(string $nombre, string $email, string $passwordHash): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, rol, estado)
             VALUES (:nombre, :email, :password_hash, :rol, :estado)'
        );
        $statement->execute([
            'nombre' => $nombre,
            'email' => $email,
            'password_hash' => $passwordHash,
            'rol' => 'estudiante',
            'estado' => 'activo',
        ]);

        return (int) $this->connection->lastInsertId();
    }
}
