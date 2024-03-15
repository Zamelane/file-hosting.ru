import { useAuthStore } from "@/stores/auth"

export const checkAuth = async () => {
  const store = useAuthStore()

  if (store.userToken && store.isCheckAuth) {
    return true
  }

  const token = localStorage.getItem('token')

  if (token) {
    store.setToken(token)
    return true
  }

  return false
}