import "bootstrap/dist/css/bootstrap.css"
import "bootstrap/dist/js/bootstrap.js"
import { createApp } from 'vue'
import { createPinia } from "pinia"
import App from './App.vue'
import './registerServiceWorker'
import router from './router'

const pinia = createPinia()

createApp(App)
.use(pinia)
.use(router)
.mount('#app')