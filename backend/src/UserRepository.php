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

    // Busca um usuário pelo id; devolve o usuário ou null se não existir
    public function findById(int $id): ?array
    {
        $pdo = Database::connect();

        // Utilizar prepare para dados que virão de fora (id): o ? é o espaço que o dado vai preencher
        $stmt = $pdo->prepare('SELECT id, name, email, login, active FROM users WHERE id = ?');
        $stmt->execute([$id]); // envia o dado separado do comando (proteção contra SQL injection)
        $user = $stmt->fetch(); // pega a linha encontrada, ou false se não achou nada

        // Não existe usuário com esse id (inativo também existe, só "não achado" cai aqui)
        if ($user === false) {
            return null;
        }

        $user['active'] = (bool) $user['active']; // 1/0 do MySQL vira true/false no JSON

        return $user;
    }
}
