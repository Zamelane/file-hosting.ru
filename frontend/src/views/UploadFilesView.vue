<template>
  <div class="p-3">
    <p class="fs-2 fw-bold">Загрузить файлы</p>
    <div class="input-group mb-3">
      <input type="file" class="form-control" id="files" ref="files" v-on:change="selectFile" multiple>
      <label class="input-group-text" for="files">Файлы для загрузки</label>
    </div>
    <div v-if="filesUploaded.length > 0">
      <hr />
      <p class="h-3 fw-bold">Файлы для загрузки</p>
      <div class="d-flex flex-column gap-2">
        <div class="card p-2 d-flex flex-row align-items-center" v-for="(file, key) in filesUploaded" :key="key">
          <div class="flex-grow-1">{{ file.name }}</div>
          <div class="text-success h-5" v-if="file.isUploaded">Загружено</div>
          <div class="text-danger h-5" v-if="file.isError">Отказано</div>
          <div class="text-secondary h-5" v-if="!file.isUploaded && !file.isError">В ожидании</div>
        </div>
      </div>
      <hr />
    </div>
    <div @click="upload" class="btn btn-success">Загрузить</div>
  </div>
</template>

<script setup>
import { URL_API } from '@/config';
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue'

let files = ref(null)
let filesUploaded = ref([])

const authStore = useAuthStore()
const { userToken } = authStore

function selectFile() {
  filesUploaded.value = files.value.files
  for (let v of filesUploaded.value) {
    v.isUploaded = false
    v.isError = false
  }
  console.log(files.value.files)
}

function upload() {
  let formData = new FormData();
  for (var i = 0; i < filesUploaded.value.length; i++) {
    let file = filesUploaded.value[i];
    formData.append('files[' + i + ']', file);
  }

  fetch(URL_API + '/api-file/files', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + userToken
    },
    body: formData
  }).then(async (data) => {
    const res = await data.json()
    console.log(res)
  })
    /*.finally(() => {
      isProcessLoading.value = false
    })*/

}
</script>