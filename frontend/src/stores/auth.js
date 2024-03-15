import { defineStore } from "pinia"
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const userToken = ref(null)
  const isCheckAuth = ref(false)

  function setToken(token) {
    localStorage.setItem('token', token)
    userToken.value = token
    isCheckAuth.value = true
  }
  function clearToken() {
    localStorage.removeItem('token')
    userToken.value = null
    isCheckAuth.value = false
  }

  return {userToken, isCheckAuth, setToken, clearToken}
})