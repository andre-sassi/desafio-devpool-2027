<script setup lang="ts"></script>

<template>
  <!-- Barra do topo: fica em todas as telas -->
  <header class="topbar">
    <div class="topbar-content">
      <span class="topbar-logo">GU</span>
      <h1>Gestão de Usuários</h1>
    </div>
  </header>

  <!-- Aqui o Router coloca a tela da rota atual -->
  <main class="container">
    <RouterView />
  </main>
</template>
