<?php

require_once __DIR__ . '/UserRepository.php';

class UserService
{
    // O Service usa o Repository para falar com o banco
    private UserRepository $repository;

    // O construtor roda automaticamente no "new UserService()"
    public function __construct()
    {
        $this->repository = new UserRepository();
    }


    // Confere os dados contra as regras de negócio do PDF.
    // Devolve uma lista de mensagens de erro (vazia = tudo certo).
    public function validate(array $data): array
    {
        $errors = [];

        // Regra 2: nome obrigatório, mínimo de 3 caracteres (sem contar espaços nas pontas)
        $name = trim($data['name'] ?? ''); // ?? '' = se o campo não veio, usa texto vazio
        if (mb_strlen($name) < 3) { // mb_strlen conta letras (strlen contaria bytes: "ã" ocupa 2)
            $errors[] = 'O nome é obrigatório e deve ter no mínimo 3 caracteres.';
        }

        // Regra 4: login obrigatório, mínimo de 3 caracteres (trim tira os espaços das pontas)
        $login = trim($data['login'] ?? '');
        if (mb_strlen($login) < 3) {
            $errors[] = 'O login é obrigatório e deve ter no mínimo 3 caracteres.';
        }

        // Regra 5: senha obrigatória, mínimo de 8 caracteres (sem trim: espaço na senha é proposital)
        $password = $data['password'] ?? '';
        if (mb_strlen($password) < 8) {
            $errors[] = 'A senha é obrigatória e deve ter no mínimo 8 caracteres.';
        }

        // Regra 3: e-mail obrigatório e em formato válido (a unicidade é checada no cadastro, pois precisa do banco)
        $email = trim($data['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // filter_var devolve false se o formato for inválido
            $errors[] = 'O e-mail é obrigatório e deve estar em um formato válido.';
        }

        // Regra 6: active é opcional (null = não veio); se vier, precisa ser booleano (true/false)
        $active = $data['active'] ?? null;
        if (isset($active) && !is_bool($active)) {
            $errors[] = 'A situação não está no formato correto.';
        }

        return $errors; // lista vazia = dados válidos
    }

    // Valida os dados informados no create e retorna o usuário recém criado, ou barra e retorna erro.
    public function create(array $data): array
    {
        $errors = $this->validate($data);

        // Se o e-mail existe, retorna erro
        if ($this->repository->emailExists(trim($data['email'] ?? ''))) {
            $errors[] = 'Este e-mail já está cadastrado.';
        }

        // Se o login existe, retorna erro
        if ($this->repository->loginExists(trim($data['login'] ?? ''))) {
            $errors[] = 'Este login já está cadastrado.';
        }

        //Se houver algum erro, nem chega ao banco
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $name   = trim($data['name']);
        $email  = trim($data['email']);
        $login  = trim($data['login']);
        $hash   = password_hash($data['password'], PASSWORD_DEFAULT); // regra 5: nunca grava a senha pura
        $active = $data['active'] ?? true; // padrão true

        // Grava; se duas requisições simultâneas passarem pela checagem, o UNIQUE do banco barra a segunda
        try {
            $id = $this->repository->create($name, $email, $login, $hash, $active);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) { // 1062 = "Duplicate entry" do MySQL
                return ['errors' => ['E-mail ou login já cadastrado.']];
            }
            throw $e; // qualquer outro erro do banco não é problema do usuário: deixa subir
        }
        // Devolve o usuário recém-criado, buscado do banco (já sem a senha)
        return ['user' => $this->repository->findById($id)];
    }
}
