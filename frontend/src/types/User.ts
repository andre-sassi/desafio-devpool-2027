export interface User {
    id: number;
    name: string;
    email: string;
    login: string;
    active: boolean;
}

// Dados que a tela ENVIA para a API (cadastro e edição)
export interface UserPayload {
    name: string;
    email: string;
    login: string;
    password?: string;
    active?: boolean;
}