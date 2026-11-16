<?php

require_once __DIR__ . '/Database.php';

class UserRepository
{
    // Busca todos os usuários cadastrados
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

    // Grava um novo usuário e devolve o id gerado
    public function create(string $name, string $email, string $login, string $passwordHash, bool $active): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, login, password_hash, active) VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([$name, $email, $login, $passwordHash, (int) $active]);

        return (int) $pdo->lastInsertId();
    }

    // Verifica no banco se o email já está vinculado a outro id
    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?'); // id <> ? ignora o próprio usuário no update
        $stmt->execute([$email, $ignoreId ?? 0]); // no cadastro passamos 0 (nenhum usuário tem id 0)

        return $stmt->fetch() !== false;
    }

    // Verifica no banco se o login já está vinculado a outro id
    public function loginExists(string $login, ?int $ignoreId = null): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ? AND id <> ?'); // id <> ? ignora o próprio usuário no update
        $stmt->execute([$login, $ignoreId ?? 0]); // no cadastro passamos 0 (nenhum usuário tem id 0)

        return $stmt->fetch() !== false;
    }

    // Atualiza um usuário, se a senha for null, a senha atual é mantida.
    public function update(int $id, string $name, string $email, string $login, ?string $passwordHash, bool $active): void
    {
        $pdo = Database::connect();

        if ($passwordHash === null) {
            // Sem senha nova
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, login = ?, active = ? WHERE id = ?');
            $stmt->execute([$name, $email, $login, (int) $active, $id]);
        } else {
            // Com senha nova
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, login = ?, password_hash = ?, active = ? WHERE id = ?');
            $stmt->execute([$name, $email, $login, $passwordHash, (int) $active, $id]);
        }
    }
}
