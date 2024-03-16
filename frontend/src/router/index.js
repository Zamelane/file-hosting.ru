import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { checkAuth } from './checkAuth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    children: [
      {
        path: 'myfiles',
        name: 'myfiles',
        component: () => import('../views/MyFilesView.vue')
      },
      {
        path: 'myfiles/:id',
        name: 'myfilesid',
        component: () => import('../views/FileEdit.vue')
      },
      {
        path: 'upload',
        name: 'upload',
        component: () => import('../views/UploadFilesView.vue')
      },
      {
        path: 'sharedme',
        name: 'sharedme',
        component: () => import('../views/SharedFilesView.vue')
      }
    ]
  },
  {
    path: '/auth',
    name: 'auth',
    component: () => import('../views/AuthView.vue')
  },
  {
    path: '/disauth',
    name: 'disauth',
    component: () => import('../views/DisAuthView.vue')
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFoundView.vue')
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  linkExactActiveClass: 'active',
  routes
})

router.beforeEach(async (to/*, from*/) => {
  const isAuth = await checkAuth()
  console.log(isAuth)

  // Если пользователь не авторизован
  if (!isAuth && to.name !== 'auth') {
    return { name: 'auth'}
  }

  // Если пользователь авторизован
  if (isAuth && to.name == 'auth') {
    return { name: 'home' }
  }
})

export default router
