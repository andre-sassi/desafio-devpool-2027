<template>
    <!-- Cabeçalho da página: título + botão de cadastro -->
    <div class="page-header">
        <h2>Usuários</h2>
        <RouterLink to="/users/new" class="btn btn-primary">+ Novo usuário</RouterLink>
    </div>

    <!-- Mensagem de sucesso (ex.: após excluir) -->
    <p v-if="success" class="alert alert-success">{{ success }}</p>

    <div class="card">
        <!-- Só um destes aparece: carregando, erro, vazio ou a tabela -->
        <p v-if="loading" class="state">Carregando...</p>
        <p v-else-if="error" class="alert alert-error">{{ error }}</p>
        <p v-else-if="users.length === 0" class="state">Nenhum usuário cadastrado.</p>
        <table v-else class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Login</th>
                    <th>Situação</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td class="text-muted">{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.login }}</td>
                    <td>
                        <!-- Etiqueta verde para ativo, cinza para inativo -->
                        <span :class="['badge', user.active ? 'badge-active' : 'badge-inactive']">
                            {{ user.active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <RouterLink :to="`/users/${user.id}/edit`" class="btn btn-outline btn-sm">Editar</RouterLink>
                            <!-- Abre o modal de confirmação (requisito 11) -->
                            <button class="btn btn-danger btn-sm" @click="askDelete(user)">Excluir</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal de confirmação: só aparece quando há um usuário escolhido para excluir -->
    <!-- @click.self: clicar no fundo escuro (fora da caixa) também cancela -->
    <div v-if="userToDelete" class="modal-overlay" @click.self="cancelDelete">
        <div class="modal">
            <h3>Excluir usuário</h3>
            <p>
                Tem certeza que deseja excluir <strong>{{ userToDelete.name }}</strong>?
                Essa ação não pode ser desfeita.
            </p>
            <div class="modal-actions">
                <button class="btn btn-secondary" :disabled="deleting" @click="cancelDelete">Cancelar</button>
                <!-- Travado durante a exclusão (loading da chamada, requisito 12) -->
                <button class="btn btn-danger-solid" :disabled="deleting" @click="confirmDelete">
                    {{ deleting ? 'Excluindo...' : 'Confirmar' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { listUsers, deleteUser } from '@/services/api'
import type { User } from '@/types/User'

// Estado da tela: quando um ref muda, o Vue redesenha a tela sozinho
const users = ref<User[]>([])   // a lista que veio da API
const loading = ref(true)       // está carregando?
const error = ref('')           // mensagem de erro (vazia = sem erro)
const userToDelete = ref<User | null>(null) // usuário escolhido para excluir (null = modal fechado)
const deleting = ref(false)                 // exclusão em andamento?
const success = ref('') // mensagem de sucesso (requisito 12)

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

// Mostra uma mensagem de sucesso e a apaga sozinha depois de 3 segundos
let successTimer: number | undefined
function showSuccess(message: string) {
    success.value = message
    clearTimeout(successTimer) // se já havia um timer, cancela (para a mensagem nova durar os 3 s inteiros)
    successTimer = window.setTimeout(() => {
        success.value = ''
    }, 3000)
}

// Abre o modal de confirmação para este usuário
function askDelete(user: User) {
    userToDelete.value = user
}

// Fecha o modal sem excluir
function cancelDelete() {
    userToDelete.value = null
}

// Exclui o usuário escolhido, depois que a pessoa confirmou no modal (requisito 11)
async function confirmDelete() {
    if (!userToDelete.value) return

    error.value = ''
    success.value = ''
    deleting.value = true
    try {
        const result = await deleteUser(userToDelete.value.id)
        showSuccess(result.message) // a mensagem vem da própria API e some depois de 3 s
        userToDelete.value = null      // fecha o modal
        await loadUsers()              // recarrega a lista, já sem o usuário
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Erro ao excluir usuário.'
        userToDelete.value = null
    } finally {
        deleting.value = false // terminou: destrava os botões
    }
}

// Roda assim que a tela aparece
onMounted(loadUsers)
</script>
