# Gestão de Usuários — Desafio Técnico Full-Stack

Aplicação full-stack de cadastro de usuários (CRUD): uma API REST em PHP e uma interface web em Vue.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Frontend | Vue 3 + TypeScript + Vue Router, com Vite |
| Backend | PHP puro (sem framework), servidor embutido do PHP |
| Banco de dados | MySQL 8, acessado via PDO com *prepared statements* |

## Arquitetura

```
frontend (Vue, porta 5173)                 backend (PHP, porta 8000)
  views/  → services/api.ts  ── HTTP/JSON ──→  public/index.php  (porta de entrada, CORS, rotas)
                                                  → Router.php       (método + caminho → handler)
                                                  → UserService.php  (regras de negócio, hash da senha)
                                                  → UserRepository.php (único lugar com SQL)
                                                  → Database.php     (conexão PDO, lê o .env)
                                                  → MySQL
```

O frontend nunca acessa o banco: toda operação passa pela API.

## Pré-requisitos

- **PHP 8.1+** com as extensões `pdo_mysql` e `mbstring` habilitadas (confira com `php -m`)
- **MySQL 8**
- **Node.js 20.19+** (traz o `npm`)
- **Git**

## Como rodar

### 1. Clonar o repositório

```bash
git clone https://github.com/andre-sassi/desafio-devpool-2027.git
cd desafio-devpool-2027
```

### 2. Banco de dados

Entre no MySQL como **root** (um dos scripts cria um usuário):

```bash
mysql -u root -p
```

Crie o banco e a tabela:

```sql
source backend/database/schema.sql
```

Crie o usuário da aplicação: **copie** `backend/database/create_user.example.sql` para `backend/database/create_user.sql`, troque `TROQUE_ESTA_SENHA` pela senha que quiser e rode:

```sql
source backend/database/create_user.sql
```

> O `create_user.sql` e o `.env` ficam fora do Git: cada pessoa usa as próprias credenciais.
> O usuário `app_user` só tem permissão de `SELECT`, `INSERT`, `UPDATE` e `DELETE` neste banco, e só a partir de `localhost`.

### 3. Backend

**Copie** `backend/.env.example` para `backend/.env` e coloque em `DB_PASS` a mesma senha do passo anterior. Depois:

```bash
cd backend
php -S localhost:8000 public/index.php
```

A API fica em `http://localhost:8000`. Deixe esse terminal aberto.

### 4. Frontend

Em **outro** terminal:

```bash
cd frontend
npm install
npm run dev
```

Acesse **http://localhost:5173**.

> O arquivo `frontend/.env` (já incluído) aponta para a API em `http://localhost:8000`. Ele não contém segredos: tudo no frontend é público, pois vai para o navegador.

## Endpoints da API

| Método | Rota | Sucesso | Erros |
|---|---|---|---|
| `GET` | `/users` | 200 + lista | — |
| `GET` | `/users/{id}` | 200 + usuário | 400 (id inválido), 404 |
| `POST` | `/users` | 201 + usuário criado | 400 (validação, e-mail/login duplicado, JSON inválido) |
| `PUT` | `/users/{id}` | 200 + usuário atualizado | 400, 404 |
| `DELETE` | `/users/{id}` | 200 + mensagem | 404 |

A senha nunca é devolvida pela API. Erros sempre vêm em JSON (`{"error": "..."}` ou `{"errors": [...]}`).

## Regras de negócio

- **ID** gerado pelo banco (`AUTO_INCREMENT`).
- **Nome** obrigatório, mínimo de 3 caracteres (ignorando espaços nas pontas).
- **E-mail** obrigatório, formato válido e único.
- **Login** obrigatório, mínimo de 3 caracteres e único.
- **Senha** obrigatória no cadastro, mínimo de 8 caracteres, armazenada com hash **bcrypt** (`password_hash`). Na edição é opcional: em branco, mantém a atual.
- **Active** booleano, padrão `true`.
- **Exclusão física**: o registro é removido. A desativação já é representada pelo campo `active`; um *soft delete* também deixaria e-mail e login presos pela restrição de unicidade.
- A unicidade é checada no PHP (mensagem amigável) **e** garantida no banco por `UNIQUE` (cobre requisições simultâneas).

## Exemplos de chamadas (cURL)

Rode a partir da pasta `backend` (os arquivos JSON estão em `backend/requests/`). No PowerShell, use `curl.exe`.

```bash
# Listar
curl -i http://localhost:8000/users

# Buscar por id
curl -i http://localhost:8000/users/1

# Cadastrar
curl -i -X POST http://localhost:8000/users -H "Content-Type: application/json" --data "@requests/criar-usuario.json"

# Cadastro inválido (400 com a lista de erros)
curl -i -X POST http://localhost:8000/users -H "Content-Type: application/json" --data "@requests/criar-usuario-invalido.json"

# Editar
curl -i -X PUT http://localhost:8000/users/1 -H "Content-Type: application/json" --data "@requests/editar-usuario.json"

# Excluir
curl -i -X DELETE http://localhost:8000/users/1
```

Exemplo de corpo para cadastro:

```json
{
  "name": "Ana Souza",
  "email": "ana@exemplo.com",
  "login": "ana",
  "password": "12345678"
}
```

Resposta (`201 Created`):

```json
{ "id": 1, "name": "Ana Souza", "email": "ana@exemplo.com", "login": "ana", "active": true }
```
