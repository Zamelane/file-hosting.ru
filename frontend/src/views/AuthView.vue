<template>
  <div class="container-xl">
    <div class="card bg-primary-subtle border border-primary-subtle rounded-3">
      <p class="title h4 text-primary-emphasis">
        {{ isReg ? "Регистрация" : "Вход" }}
      </p>
      <div class="px-4 py-3">
        <div class="mb-3" v-if="isReg">
          <label for="first_name" class="form-label text-primary-emphasis">Имя</label>
          <input :class="{ 'is-invalid': isInvalid }" :disabled="isProcess" v-model="first_name"
            class="form-control border border-primary-subtle" id="first_name" placeholder="Твоё имя">
        </div>
        <div class="mb-3" v-if="isReg">
          <label for="last_name" class="form-label text-primary-emphasis">Фамилия</label>
          <input :class="{ 'is-invalid': isInvalid }" :disabled="isProcess" v-model="last_name"
            class="form-control border border-primary-subtle" id="last_name" placeholder="Твоя фамилия">
        </div>
        <div class="mb-3">
          <label for="login" class="form-label text-primary-emphasis">Почта</label>
          <input :class="{ 'is-invalid': isInvalid }" :disabled="isProcess" v-model="login"
            class="form-control border border-primary-subtle" id="login" placeholder="Твоя почта">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label text-primary-emphasis">Пароль</label>
          <input :class="{ 'is-invalid': isInvalid }" :disabled="isProcess" v-model="password" type="password"
            class="form-control border border-primary-subtle" id="password" placeholder="Твой пароль при регистрации">
        </div>
        <div class="d-flex justify-content-between">
          <button @click="auth" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!isProcess" />
            <span role="status" :hidden="isProcess">
              {{ isReg ? "Зарегистрироваться" : "Войти" }}
            </span>
          </button>
          <button class="btn btn-link" @click="authSelect">
            {{ isReg ? "Войти" : "Зарегистрироваться" }}
          </button>
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
let isReg = ref(false)

let first_name = ref("")
let last_name = ref("")

const authStore = useAuthStore()

let isProcess = ref(false)

function authSelect() {
  isReg.value = !isReg.value
}

async function auth() {
  isProcess.value = true

  let body = {
    email: login,
    password
  }

  if (isReg.value) {
    body.first_name = first_name.value
    body.last_name = last_name.value
  }

  await fetch(URL_API + '/api-file/' + (isReg.value ? 'registration' : 'authorization'), {
    method: 'post',
    body: JSON.stringify(body),
    headers: {
      'Content-Type': 'application/json'
    }
  })
    .then(async (data) => {
      return await data.json()
    })
    .then(async (data) => {
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