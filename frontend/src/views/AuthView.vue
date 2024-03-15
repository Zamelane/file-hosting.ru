<template>
  <div class="container-xl">
    <div class="card bg-primary-subtle border border-primary-subtle rounded-3">
      <p class="title h4 text-primary-emphasis">Войти</p>
      <div class="px-4 py-3">
        <div class="mb-3">
          <label for="login" class="form-label text-primary-emphasis">Почта</label>
          <input :class="{ 'is-invalid': isInvalid}" :disabled="isProcess" v-model="login" class="form-control border border-primary-subtle" id="login" placeholder="Твоя почта">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label text-primary-emphasis">Пароль</label>
          <input :class="{ 'is-invalid': isInvalid}" :disabled="isProcess" v-model="password" type="password" class="form-control border border-primary-subtle" id="password" placeholder="Твой пароль при регистрации">
        </div>
        <div class="d-flex justify-content-between">
          <button @click="auth" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!isProcess"/>
            <span role="status" :hidden="isProcess">Войти</span>
          </button>
          <button class="btn btn-link">Зарегистрироваться</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { URL_API } from '@/config';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue'
let login = ""
let password = ""

let isInvalid = ref(false)

const authStore = useAuthStore()

let isProcess = ref(false)

async function auth() {
  isProcess.value = true
  
  await fetch(URL_API + '/api-file/authorization', {
    method: 'post',
    body: JSON.stringify({
      email: login,
      password
    }),
    headers: {
      'Content-Type': 'application/json'
    }
  })
  .then(async (data) => {
    return await data.json()
  })
  .then((data) => {
    if (data.success == true) {
      authStore.setToken(data.token)
      router.push({ name: 'home' })
    }
  })

  isInvalid.value = true
  isProcess.value = false
}
</script>

<style lang="scss" scoped>
#view {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.card {
  min-width: 350px;
}
.title {
  margin: 10px auto 0 auto;
}
</style>