<?php

class UserService
{
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
}
