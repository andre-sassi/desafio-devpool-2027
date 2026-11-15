<?php

require_once __DIR__ . '/Database.php';

class UserRepository
{
    public function findAll(): array
    {
        $pdo = Database::connect();

        // Seleciona todos os dados, exceto o password, ordenando por id
        $stmt = $pdo->query('SELECT id, name, email, login, active FROM users ORDER BY id');

        $users = $stmt->fetchAll();

        // O MySQL devolve active como 1/0; convertemos para true/false no JSON
        $resultado = [];
        foreach ($users as $user) {
            $user['active'] = (bool) $user['active'];
            $resultado[] = $user;
        }

        return $resultado;
    }
}