<template>
  <ul class="nav nav-pills">
    <li class="nav-item">
      <router-link class="nav-link" to="/">Домашняя страница</router-link>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Файлы</a>
      <ul class="dropdown-menu">
        <li><router-link class="nav-link" to="/myfiles">Мои</router-link></li>
        <li><router-link class="dropdown-item" to="/upload">Загрузить новые</router-link></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Со мной поделились</a></li>
      </ul>
    </li>
    <li class="nav-item auth">
      <button @click="disauth" class="btn btn-light">Выйти</button>
    </li>
  </ul>
</template>

<script setup>
import router from '@/router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore()

function disauth() {
  authStore.clearToken()
  router.push({ name: 'auth' })
}
</script>

<style lang="scss" scoped>
.auth {
  margin-left: auto;
}
</style>