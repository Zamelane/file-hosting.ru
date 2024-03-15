<template>
  <p class="fs-2 fw-bold p-3">Редактирование файла</p>

  <router-link to="/myfiles" class="btn btn-primary m-2">← Ко всем файлам</router-link>

  <div v-if="selectedFile">
    <div class="container-fluid border rounded-top p-4 m-2 d-flex flex-column gap-2">
      <h4 class="d-flex flex-row gap-2">
        <input class="form-control" placeholder="test" v-model="newName"> 
        <span class="badge text-bg-secondary" v-if="newName == selectedFile.name">{{ selectedFile.extension }}</span>
        <button @click="rename" class="btn btn-success" v-if="newName != selectedFile.name">
          <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!processRename" />
          <span role="status" :hidden="processRename">Сохранить</span>
        </button>
      </h4>

      <div class="card">
        <div class="card-header">
          Общий доступ
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex" v-for="user in selectedFile.access" :key="user.email">
            <div class="flex-grow-1">
              {{ user.fullName }}
              <span class="badge text-bg-success" :class="{'text-bg-success': user.type == 'author', 'text-bg-warning': user.type != 'author'}">{{ user.email }}</span>
            </div>
            <div class="btn-group" role="group" v-if="user.type != 'author'">
              <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false" :disabled="user.processDeleted">
                <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!user.processDeleted" />
                Действие
              </button>
              <ul class="dropdown-menu" v-if="!user.processDeleted">
                <li><a @click="deletePrivilegies(user)" class="dropdown-item" href="#">Отозвать доступ</a></li>
              </ul>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <div class="container-fluid border rounded-bottom m-2 p-4">
      <p class="h4">Предоставить доступ</p>
      <div class="mb-4">
        <label for="email" class="form-label">Email адрес пользователя</label>
        <input v-model="userEmail" :disabled="processAddUser" :class="{ 'is-invalid': error, 'border-success': !error }"
          type="email" class="form-control" id="email" placeholder="name@example.com">
        <p v-if="error" class="text-danger h-5">{{ error }}</p>
      </div>
      <button @click="addPrivilegies" class="btn btn-success">
        <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!processAddUser" />
        <span role="status" :hidden="processAddUser">Поделиться</span>
      </button>
    </div>
  </div>

  <div v-if="isProcessLoading">
    <div class="container-fluid border rounded-top p-4 m-2 d-flex flex-column gap-2 placeholder-glow">
      <h4 class="placeholder"> <span class="badge text-bg-secondary placeholder"></span></h4>

      <div class="card">
        <div class="card-header placeholder">
          Общий доступ
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item placeholder">
            <p class="placeholder"></p>
            <span class="badge text-bg-success placeholder"></span>
          </li>
        </ul>
      </div>
    </div>

    <div class="container-fluid border rounded-bottom m-2 p-4 placeholder-glow">
      <p class="h4 placeholder" style="width: 230px;"></p>
      <div class="mb-4">
        <label for="email" class="form-label placeholder">Email адрес пользователя</label>
        <input type="email" class="form-control placeholder" id="email" placeholder="name@example.com">
      </div>
      <button class="btn placeholder">Поделиться файлом</button>
    </div>
  </div>

  <div v-if="notFound" class="card border-danger mb-3">
    <div class="card-header">Файл не найлен</div>
    <div class="card-body text-danger">
      <h5 class="card-title">Файл с id "{{ fileId }}" не найден</h5>
      <p class="card-text">Возможно файл был удалён или ошибка в ссылке</p>
    </div>
  </div>

</template>

<script setup>
import { URL_API } from '@/config';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue'

const authStore = useAuthStore()

const { userToken } = authStore

let files = ref([])
let selectedFile = ref(null)
let isProcessLoading = ref(true)
let notFound = ref(false)
let newName = ref("")
let processRename = ref(false)

let processAddUser = ref(false)
let userEmail = ref("")
let error = ref(null)

const errors = {
  404: 'Пользователь не найден'
}

const fileId = router.currentRoute._value.params.id

fetch(URL_API + '/api-file/files/disk', {
  method: 'get',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + userToken
  }
}).then(async (data) => {
  files.value = await data.json()
  files.value.forEach((elem) => {
    if (elem.file_id == fileId) {
      selectedFile.value = elem
      newName.value = elem.name
    }
  })
  if (!selectedFile.value) {
    notFound.value = true
  }
})
  .finally(() => {
    isProcessLoading.value = false
  })

async function addPrivilegies() {
  if (userEmail.value.length < 3) {
    error.value = "Введите почту. Минимум 3 символа"
    return
  }

  error.value = null
  processAddUser.value = true
  fetch(URL_API + '/api-file/files/' + fileId + '/accesses', {
    method: 'post',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + userToken
    },
    body: JSON.stringify({
      email: userEmail.value
    })
  }).then(async (data) => {
    const result = await data.json()

    if (data.status == 200 && !result.email) {
      selectedFile.value.access = result
      console.log(selectedFile.value)
      return
    }

    if (errors[data.status]) {
      error.value = errors[data.status]
    } else {
      error.value = 'Неизвестная ошибка. У вас есть интернет ?'
    }
  })
    .finally(() => {
      processAddUser.value = false
    })
}

async function deletePrivilegies(user) {
  user.processDeleted = true
  fetch(URL_API + '/api-file/files/' + fileId + '/accesses', {
    method: 'delete',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + userToken
    },
    body: JSON.stringify({
      email: user.email
    })
  }).then(async (data) => {
    const result = await data.json()

    if (data.status == 200 && !result.email) {
      selectedFile.value.access = result
      return
    }
  })
    .finally(() => {
      user.processDeleted = false
    })
}

async function rename() {
  processRename.value = true
  fetch(URL_API + '/api-file/files/' + fileId, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + userToken
    },
    body: JSON.stringify({
      name: newName.value
    })
  }).then(async (data) => {
    const result = await data.json()

    if (data.status == 200 && !result.name && result.code == 200) {
      selectedFile.value.name = newName.value
      return
    }
  })
    .finally(() => {
      processRename.value = false
    })
}
</script>