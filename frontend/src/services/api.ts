import type { User, UserPayload } from '@/types/User'

// Endereço da API, vindo do .env do frontend
const API_URL = import.meta.env.VITE_API_URL

// Faz uma requisição à API e devolve o JSON da resposta.
// Se a API responder com erro (400, 404, 500...), lança um Error com a mensagem dela.
async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
    let response: Response

    // Sem resposta nenhuma (API desligada, sem rede): o fetch falha antes de qualquer status
    try {
        response = await fetch(`${API_URL}${path}`, {
            ...options,
            headers: { 'Content-Type': 'application/json' },
        })
    } catch {
        throw new Error('Não foi possível conectar à API. Verifique se o servidor está ligado.')
    }

    const data = await response.json()

    // response.ok = status entre 200 e 299
    if (!response.ok) {
        // A API manda 'error' (texto) ou 'errors' (lista); juntamos tudo numa mensagem só
        const message = data.error ?? data.errors?.join(' ') ?? 'Erro inesperado.'
        throw new Error(message)
    }

    return data
}

// Lista todos os usuários (GET /users)
export function listUsers(): Promise<User[]> {
    return request<User[]>('/users');
}

// Busca um usuário pelo id (GET /users/{id})
export function getUser(id: number): Promise<User> {
    return request<User>(`/users/${id}`);
}

// Cadastra um usuário (POST /users)
export function createUser(payload: UserPayload): Promise<User> {
    return request<User>('/users', {
        method: 'POST',
        body: JSON.stringify(payload), // transforma o objeto em texto JSON
    })
}

// Edita um usuário (PUT /users/{id})
export function updateUser(id: number, payload: UserPayload): Promise<User> {
    return request<User>(`/users/${id}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
    })
}