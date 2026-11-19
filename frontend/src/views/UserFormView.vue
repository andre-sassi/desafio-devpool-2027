<template>
  <!-- O título muda conforme o modo: cadastro (/users/new) ou edição (/users/:id/edit) -->
  <h2>{{ isEdit ? 'Editar usuário' : 'Novo usuário' }}</h2>

  <!-- Na edição, enquanto busca o usuário na API -->
  <p v-if="loading">Carregando...</p>

  <!-- @submit.prevent: chama o save() e impede o navegador de recarregar a página -->
  <form v-else @submit.prevent="save">
    <!-- v-model liga o campo ao form, nos dois sentidos -->
    <label>
      Nome
      <input v-model="form.name" type="text" />
    </label>

    <label>
      E-mail
      <input v-model="form.email" type="email" />
    </label>

    <label>
      Login
      <input v-model="form.login" type="text" />
    </label>

    <label>
      <!-- Na edição a senha é opcional: vazia = mantém a atual -->
      {{ isEdit ? 'Senha (deixe em branco para manter a atual)' : 'Senha' }}
      <input v-model="form.password" type="password" />
    </label>

    <label>
      <input v-model="form.active" type="checkbox" />
      Ativo
    </label>

    <!-- Erros da API (validação 400, usuário não encontrado 404, API fora do ar) -->
    <p v-if="error">{{ error }}</p>

    <!-- :disabled trava o botão durante o envio (evita clique duplo e cadastro duplicado) -->
    <button type="submit" :disabled="saving">
      {{ saving ? 'Salvando...' : 'Salvar' }}
    </button>

    <RouterLink to="/users">Cancelar</RouterLink>
  </form>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getUser, createUser, updateUser } from '@/services/api'
import type { UserPayload } from '@/types/User'

const route = useRoute()   // lê a URL atual (os parâmetros, como o :id)
const router = useRouter() // navega para outra tela pelo código

// Tem :id na URL? Então é edição. Senão, é cadastro.
const id = route.params.id ? Number(route.params.id) : null
const isEdit = id !== null

// Os dados do formulário: cada campo da tela fica ligado a uma destas propriedades
const form = ref<UserPayload>({
  name: '',
  email: '',
  login: '',
  password: '',
  active: true,
})

const loading = ref(false) // buscando o usuário (só na edição)
const saving = ref(false)  // enviando para a API
const error = ref('')      // mensagem de erro da API

// Na edição, busca o usuário e preenche o formulário
onMounted(async () => {
  if (id === null) return // cadastro: nada para buscar

  loading.value = true
  try {
    const user = await getUser(id)
    // A senha fica vazia: na edição, vazio = "manter a atual"
    form.value = { name: user.name, email: user.email, login: user.login, password: '', active: user.active }
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Erro ao carregar o usuário.'
  } finally {
    loading.value = false
  }
})

// Envia o formulário: PUT se for edição, POST se for cadastro
async function save() {
  saving.value = true
  error.value = ''
  try {
    if (id !== null) {
      await updateUser(id, form.value)
    } else {
      await createUser(form.value)
    }
    router.push('/users') // deu certo: volta para a listagem
  } catch (e) {
    // Os erros de validação da API (400) chegam aqui, já juntos numa mensagem pelo api.ts
    error.value = e instanceof Error ? e.message : 'Erro ao salvar.'
  } finally {
    saving.value = false
  }
}
</script>
