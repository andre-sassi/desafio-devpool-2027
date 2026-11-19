<template>
    <h2>Usuários</h2>
    <RouterLink to="/users/new">Novo usuário</RouterLink>

    <p v-if="loading">Carregando...</p>
    <p v-else-if="error">{{ error }}</p>
    <p v-else-if="users.length === 0">Nenhum usuário cadastrado.</p>
    <table v-else>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Login</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.login }}</td>
                <td>{{ user.active ? 'Ativo' : 'Inativo' }}</td>
                <td><RouterLink :to="`/users/${user.id}/edit`">Editar</RouterLink></td>
            </tr>
        </tbody>
    </table>

</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { listUsers } from '@/services/api'
import type { User } from '@/types/User'

// Estado da tela: quando um ref muda, o Vue redesenha a tela sozinho
const users = ref<User[]>([])   // a lista que veio da API
const loading = ref(true)       // está carregando?
const error = ref('')           // mensagem de erro (vazia = sem erro)

// Busca os usuários na API
async function loadUsers() {
    loading.value = true
    error.value = ''
    try {
        users.value = await listUsers()
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Erro ao carregar usuários.'
    } finally {
        loading.value = false // dando certo ou errado, terminou de carregar
    }
}

// Roda assim que a tela aparece
onMounted(loadUsers)
</script>
