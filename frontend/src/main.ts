import './assets/main.css' // estilo global (cores, botões, tabela, formulário)
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(router)

app.mount('#app')
