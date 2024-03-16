<template>
  <div class="p-3">
    <p class="fs-2 fw-bold">Загрузить файлы</p>
    <p class="fs-5 text-secondary">Разрешены следующие форматы: doc, pdf, docx, zip, jpeg, jpg, png</p>
    <div class="input-group mb-3">
      <input type="file" class="form-control" id="files" ref="files" v-on:change="selectFile" multiple>
      <label class="input-group-text" for="files">Файлы для загрузки</label>
    </div>
    <div v-if="filesUploaded.length > 0">
      <hr />
      <p class="h-3 fw-bold">Файлы для загрузки</p>
      <div class="d-flex flex-column gap-2">
        <div class="card p-2 d-flex flex-row align-items-center" v-for="(file, key) in filesUploaded" :key="key">
          <div class="flex-grow-1">{{ file.name }} {{ file.newName ? "→ " + file.newName : "" }}</div>
          <div v-if="!file.isProcess">
            <div class="text-success h-5" v-if="file.isUploaded">Загружено</div>
            <div class="text-danger h-5" v-if="file.isError">Отказано</div>
            <div class="text-secondary h-5" v-if="!file.isUploaded && !file.isError">В ожидании</div>
          </div>
          <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!file.isProcess"></span>
        </div>
      </div>
      <hr />
      <p v-if="isError" class="text-danger">
        <span v-if="isError === true">Ошибка загрузки файлов. Может вы пытаетесь загрузить слишком большой файл ?</span>
        {{ isError !== true ? isError : "" }}
      </p>
    </div>
    <button @click="upload" class="btn btn-success" :disabled="isProcess || !fileFound">
      <span class="spinner-border spinner-border-sm" aria-hidden="true" :hidden="!isProcess"></span>
      <span role="status" :hidden="isProcess">Загрузить</span>
    </button>
  </div>
</template>

<script setup>
import { URL_API } from '@/config';
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue'

let files = ref(null)
let filesUploaded = ref([])
let isProcess = ref(false)
let isError = ref(false)
let fileFound = ref(false)

const authStore = useAuthStore()
const { userToken } = authStore

function selectFile() {
  fileFound.value = true
  filesUploaded.value = files.value.files
  for (let v of filesUploaded.value) {
    v.isUploaded = false
    v.isError = false
    v.isProcess = false
  }
  console.log(files.value.files)
}

async function upload() {
  isProcess.value = true
  isError.value = false
  let formData
  for (var i = 0; i < filesUploaded.value.length; i++) {
    filesUploaded.value[i].isProcess = true
    let file = filesUploaded.value[i];
    formData = new FormData();
    if (file.isUploaded) {
      continue
    }
    formData.append('files[0]', file);
    await fetch(URL_API + '/api-file/files', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + userToken
      },
      body: formData
    }).then(async (data) => {
      const res = await data.json()
      filesUploaded.value[i].isUploaded = res[0].success
      filesUploaded.value[i].isError = !res[0].success
      if (res?.[0]?.name && res[0].name != filesUploaded.value[i].name) {
        filesUploaded.value[i].newName = res[0].name
      }
      fileFound.value = false
    })
      .catch(() => {
        isError.value = true
        filesUploaded.value[i].isError = true
      })
      .finally(() => {
        filesUploaded.value[i].isProcess = false
      })
  }
  isProcess.value = false

}
</script>