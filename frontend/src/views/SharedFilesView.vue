<template>
  <p class="fs-2 fw-bold p-3">Со мной поделились</p>

  <p class="fs-4 fw-bold p-3" v-if="!isProcessLoading && !files.length">
    Файлов нет. С вами никто не поделился :(
  </p>


  <div class="container-fluid d-flex gap-2 flex-column file-container">
    <div class="card" v-for="file in files" :key="file.file_id">
      <div class="card-body d-flex align-items-center">
        <p class="flex-grow-1 mb-0">
          {{ file.name }}
          <span class="badge text-bg-secondary">
            {{ file.extension }}
          </span>
        </p>
        <div class="d-flex gap-2">

          <button :disabled="file.isProcess" @click="download(file)" type="button" class="btn btn-outline-success d-flex gap-2 align-items-center">
            <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!file.isProcess"></span>
            <span role="status" :hidden="file.isProcess">Скачать</span>
          </button>
        </div>
      </div>
    </div>

    <div v-for="n in 10" :key="n" class="container-fluid d-flex gap-2 flex-column file-container">
      <div class="card" aria-hidden="true" v-if="isProcessLoading">
        <div class="card-body d-flex align-items-center placeholder-glow">
          <p class="flex-grow-1 mb-0 placeholder" style="max-width: 250px;"></p>
          <p class="flex-grow-1"></p>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success d-flex gap-2 align-items-center placeholder">
              <span role="status">Скачать</span>
            </button>
            <button type="button" class="btn btn-outline-primary placeholder">Подробнее</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { URL_API } from '@/config';
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue'

const authStore = useAuthStore()

const { userToken } = authStore

let files = ref([])
let isProcessLoading = ref(true)

fetch(URL_API + '/api-file/files/shared', {
  method: 'get',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + userToken
  }
}).then(async (data) => {
  files.value = await data.json()
  files.value.forEach((elem) => {
    elem.isProcess = false
  })
})
  .finally(() => {
    isProcessLoading.value = false
  })

async function download(file) {
  file.isProcess = true
  await fetch(file.url, {
    method: 'get',
    headers: {
      'Content-Type': 'multipart/form-data',
      'Authorization': 'Bearer ' + userToken
    }
  })
    .then(response => response.blob())
    .then(blob => {
      console.log(blob)
      var link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.download = file.name + '.' + file.extension;
      link.click();
    })

  file.isProcess = false
}

</script>

<style lang="scss" scoped></style>