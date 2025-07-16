<template>
  <header 
    class="masthead" 
    v-motion
    :initial="{ opacity: 0, y: -50 }"
    :enter="{ opacity: 1, y: 0 }"
    :transition="{ duration: 600, type: 'spring' }"
  >
    <div class="masthead__container">
      <div 
        class="masthead__logo" 
        v-motion
        :initial="{ opacity: 0, x: -50 }"
        :enter="{ opacity: 1, x: 0 }"
        :transition="{ duration: 600, delay: 200, type: 'spring' }"
      >
        <router-link to="/search" class="masthead__logo-link">
          <h1 class="masthead__title">
            <i class="pi pi-home animate-bounce"></i> 
            Hotel Booking
          </h1>
        </router-link>
      </div>

      <nav 
        class="masthead__nav" 
        v-motion
        :initial="{ opacity: 0, x: 50 }"
        :enter="{ opacity: 1, x: 0 }"
        :transition="{ duration: 600, delay: 400, type: 'spring' }"
      >
        <template v-if="isAuthenticated">
          <router-link 
            to="/dashboard" 
            class="masthead__nav-link hover-lift"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 600, type: 'spring' }"
          >
            DASHBOARD
          </router-link>
          <router-link 
            to="/search" 
            class="masthead__nav-link hover-lift"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 700, type: 'spring' }"
          >
            SEARCH ROOMS
          </router-link>
          <div 
            class="masthead__user"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 800, type: 'spring' }"
          >
            <span class="masthead__user-name">Welcome, {{ user?.name }}</span>
            <button @click="logout" class="masthead__logout-btn hover-lift">
              <i class="pi pi-sign-out"></i>
              Logout
            </button>
          </div>
        </template>
        <template v-else>
          <!-- Only show login button if not on login page -->
          <router-link 
            v-if="!isOnLoginPage" 
            to="/login" 
            class="masthead__nav-link masthead__nav-link--primary hover-lift"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 600, type: 'spring' }"
          >
            <i class="pi pi-sign-in"></i>
            Login
          </router-link>
        </template>
      </nav>
    </div>
  </header>
</template>

<script>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores'

export default {
  name: 'Masthead',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()

    const isAuthenticated = computed(() => authStore.isAuthenticated)
    const user = computed(() => authStore.user)
    const isOnLoginPage = computed(() => route.path === '/login')

    const logout = () => {
      authStore.logout()
      router.push('/login')
    }

    return {
      isAuthenticated,
      user,
      isOnLoginPage,
      logout
    }
  }
}
</script>

<style scoped>
.masthead {
  background: var(--gradient-primary);
  box-shadow: 0 4px 20px rgba(0, 53, 128, 0.2);
  position: sticky;
  top: 0;
  z-index: 100;
  backdrop-filter: blur(10px);
}

.masthead__container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

.masthead__logo-link {
  text-decoration: none;
  color: inherit;
  transition: transform 0.3s ease;
}

.masthead__logo-link:hover {
  transform: scale(1.05);
}

.masthead__title {
  color: white;
  font-size: 24px;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.masthead__title i {
  font-size: 28px;
  color: #febb02;
  transition: transform 0.3s ease;
}

.masthead__title:hover i {
  transform: rotate(10deg);
}

.masthead__nav {
  display: flex;
  align-items: center;
  gap: 16px;
}

.masthead__nav-link {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-weight: 500;
  padding: 10px 18px;
  border-radius: 8px;
  transition: all 0.3s ease;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
  overflow: hidden;
}

.masthead__nav-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.masthead__nav-link:hover::before {
  left: 100%;
}

.masthead__nav-link:hover {
  color: white;
  background-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.masthead__nav-link.router-link-active {
  color: white;
  background-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.masthead__nav-link--primary {
  background: linear-gradient(135deg, #febb02 0%, #f39c12 100%);
  color: #003580;
  border: none;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(254, 187, 2, 0.3);
}

.masthead__nav-link--primary:hover {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(254, 187, 2, 0.4);
}

.masthead__user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 16px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-radius: 25px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

.masthead__user-name {
  color: white;
  font-weight: 500;
  font-size: 14px;
}

.masthead__logout-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  font-size: 13px;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

.masthead__logout-btn:hover {
  color: white;
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .masthead__container {
    padding: 0 16px;
    height: 60px;
  }

  .masthead__title {
    font-size: 20px;
  }

  .masthead__nav {
    gap: 12px;
  }

  .masthead__nav-link {
    padding: 8px 12px;
    font-size: 13px;
  }

  .masthead__user {
    padding: 6px 12px;
  }

  .masthead__user-name {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .masthead__user-name {
    display: none;
  }
  
  .masthead__nav {
    gap: 8px;
  }

  .masthead__nav-link {
    padding: 6px 10px;
    font-size: 12px;
  }
}
</style>
