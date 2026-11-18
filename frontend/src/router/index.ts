import { createRouter, createWebHistory } from 'vue-router'
import UserListView from '@/views/UserListView.vue'
import UserFormView from '@/views/UserFormView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/users' },
    { path: '/users', component: UserListView },
    { path: '/users/new', component: UserFormView },
    { path: '/users/:id/edit', component: UserFormView }
  ],
})

export default router
