import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores'

// Import pages
import LoginRegister from '../pages/LoginRegister.vue'
import Dashboard from '../pages/Dashboard.vue'
import RoomSearch from '../pages/RoomSearch.vue'
import Rooms from '../pages/Rooms.vue'
import ContactInfo from '../pages/ContactInfo.vue'
import Confirmation from '../pages/Confirmation.vue'

const routes = [
  {
    path: '/',
    redirect: '/search'
  },
  {
    path: '/login',
    name: 'LoginRegister',
    component: LoginRegister,
    meta: { 
      requiresGuest: true,
      transition: 'scale'
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { 
      requiresAuth: true,
      transition: 'slide-right'
    }
  },
  {
    path: '/search',
    name: 'RoomSearch',
    component: RoomSearch,
    meta: {
      transition: 'fade'
    }
    // Removed requiresAuth - this page is now public
  },
  {
    path: '/rooms',
    name: 'Rooms',
    component: Rooms,
    meta: { 
      requiresAuth: true,
      transition: 'slide-left'
    }
  },
  {
    path: '/contact-info',
    name: 'ContactInfo',
    component: ContactInfo,
    meta: { 
      requiresAuth: true,
      transition: 'slide-up'
    }
  },
  {
    path: '/confirmation',
    name: 'Confirmation',
    component: Confirmation,
    meta: { 
      requiresAuth: true,
      transition: 'scale'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/search'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Route guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Store the intended destination for redirect after login
    sessionStorage.setItem('redirectAfterLogin', to.fullPath)
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
