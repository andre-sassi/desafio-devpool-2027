<template>
  <!-- O título muda conforme o modo: cadastro (/users/new) ou edição (/users/:id/edit) -->
  <div class="page-header">
    <h2>{{ isEdit ? 'Editar usuário' : 'Novo usuário' }}</h2>
  </div>

  <div class="card">
    <!-- Na edição, enquanto busca o usuário na API -->
    <p v-if="loading" class="state">Carregando...</p>

    <!-- @submit.prevent: chama o save() e impede o navegador de recarregar a página -->
    <form v-else class="form" @submit.prevent="save">
      <!-- v-model liga cada campo ao form, nos dois sentidos -->
      <div class="form-grid">
        <label class="field field-full">
          Nome
          <input v-model="form.name" type="text" />
        </label>

        <label class="field">
          E-mail
          <input v-model="form.email" type="email" />
        </label>

        <label class="field">
          Login
          <input v-model="form.login" type="text" />
        </label>

        <label class="field field-full">
          Senha
          <input v-model="form.password" type="password" />
          <!-- Na edição a senha é opcional: vazia = mantém a atual -->
          <span v-if="isEdit" class="hint">Deixe em branco para manter a senha atual.</span>
          <span v-else class="hint">Mínimo de 8 caracteres.</span>
        </label>

        <label class="checkbox field-full">
          <input v-model="form.active" type="checkbox" />
          Usuário ativo
        </label>
      </div>

      <!-- Erros da API (validação 400, não encontrado 404, servidor fora do ar) e sucesso -->
      <p v-if="error" class="alert alert-error" style="margin-top: 20px">{{ error }}</p>
      <p v-if="success" class="alert alert-success" style="margin-top: 20px">{{ success }}</p>

      <div class="form-actions">
        <!-- :disabled trava o botão durante o envio (evita clique duplo e cadastro duplicado) -->
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar' }}
        </button>
        <RouterLink to="/users" class="btn btn-secondary">Cancelar</RouterLink>
      </div>
    </form>
  </div>
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
const success = ref('') // mensagem de sucesso (requisito 12)

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
  success.value = ''
  try {
    if (id !== null) {
      await updateUser(id, form.value)
    } else {
      await createUser(form.value)
    }
    success.value = isEdit ? 'Usuário atualizado com sucesso.' : 'Usuário cadastrado com sucesso.'
    // espera 1,5 segundo para a pessoa ler a mensagem, depois volta para a listagem
    await new Promise((resolve) => setTimeout(resolve, 1500))
    router.push('/users')
  } catch (e) {
    // Os erros de validação da API (400) chegam aqui, já juntos numa mensagem pelo api.ts
    error.value = e instanceof Error ? e.message : 'Erro ao salvar.'
  } finally {
    saving.value = false
  }
}


</script>
