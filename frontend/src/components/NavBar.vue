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
        <li><router-link class="dropdown-item" to="/sharedme">Со мной поделились</router-link></li>
      </ul>
    </li>
    <li class="nav-item auth">
      <button @click="disauth" class="btn btn-light">
        <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!exitProcess" />
        <span role="status" :hidden="exitProcess">Выйти</span>
      </button>
    </li>
  </ul>
</template>

<script setup>
import { URL_API } from '@/config';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue'

const authStore = useAuthStore()
const { userToken } = authStore

let exitProcess = ref(false)

function disauth() {
  exitProcess.value = true
  fetch(URL_API + '/api-file/logout', {
    method: 'get',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + userToken
    }
  })
    .finally(() => {
      exitProcess.value = false
      authStore.clearToken()
      router.push({ name: 'auth' })
    })
}
</script>

<style lang="scss" scoped>
.auth {
  margin-left: auto;
}
</style>