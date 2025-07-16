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
            <span class="masthead__title-text">Hotel Booking</span>
          </h1>
        </router-link>
      </div>

      <!-- Mobile menu button -->
      <button 
        class="masthead__mobile-toggle"
        @click="toggleMobileMenu"
        :class="{ 'masthead__mobile-toggle--active': isMobileMenuOpen }"
        v-motion
        :initial="{ opacity: 0, x: 50 }"
        :enter="{ opacity: 1, x: 0 }"
        :transition="{ duration: 600, delay: 300, type: 'spring' }"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav 
        class="masthead__nav" 
        :class="{ 'masthead__nav--mobile-open': isMobileMenuOpen }"
        v-motion
        :initial="{ opacity: 0, x: 50 }"
        :enter="{ opacity: 1, x: 0 }"
        :transition="{ duration: 600, delay: 400, type: 'spring' }"
      >
        <template v-if="isAuthenticated">
          <router-link 
            to="/dashboard" 
            class="masthead__nav-link hover-lift"
            @click="closeMobileMenu"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 600, type: 'spring' }"
          >
            <i class="pi pi-chart-line"></i>
            <span class="masthead__nav-text">Dashboard</span>
          </router-link>
          <router-link 
            to="/search" 
            class="masthead__nav-link hover-lift"
            @click="closeMobileMenu"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 700, type: 'spring' }"
          >
            <i class="pi pi-search"></i>
            <span class="masthead__nav-text">Search Rooms</span>
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
              <span class="masthead__logout-text">Logout</span>
            </button>
          </div>
        </template>
        <template v-else>
          <!-- Only show login button if not on login page -->
          <router-link 
            v-if="!isOnLoginPage" 
            to="/login" 
            class="masthead__nav-link masthead__nav-link--primary hover-lift"
            @click="closeMobileMenu"
            v-motion
            :initial="{ opacity: 0, x: 30 }"
            :enter="{ opacity: 1, x: 0 }"
            :transition="{ duration: 400, delay: 600, type: 'spring' }"
          >
            <i class="pi pi-sign-in"></i>
            <span class="masthead__nav-text">Login</span>
          </router-link>
        </template>
      </nav>
    </div>
  </header>
</template>

<script>
import { computed, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores'

export default {
  name: 'Masthead',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()
    const isMobileMenuOpen = ref(false)

    const isAuthenticated = computed(() => authStore.isAuthenticated)
    const user = computed(() => authStore.user)
    const isOnLoginPage = computed(() => route.path === '/login')

    const logout = () => {
      authStore.logout()
      router.push('/login')
      closeMobileMenu()
    }

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value
    }

    const closeMobileMenu = () => {
      isMobileMenuOpen.value = false
    }

    return {
      isAuthenticated,
      user,
      isOnLoginPage,
      isMobileMenuOpen,
      logout,
      toggleMobileMenu,
      closeMobileMenu
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
  position: relative;
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
  flex-shrink: 0;
}

.masthead__title:hover i {
  transform: rotate(10deg);
}

.masthead__title-text {
  white-space: nowrap;
}

/* Mobile menu toggle button */
.masthead__mobile-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-around;
  width: 30px;
  height: 30px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 101;
}

.masthead__mobile-toggle span {
  width: 100%;
  height: 3px;
  background: white;
  border-radius: 2px;
  transition: all 0.3s ease;
  transform-origin: center;
}

.masthead__mobile-toggle--active span:nth-child(1) {
  transform: rotate(45deg) translate(6px, 6px);
}

.masthead__mobile-toggle--active span:nth-child(2) {
  opacity: 0;
}

.masthead__mobile-toggle--active span:nth-child(3) {
  transform: rotate(-45deg) translate(6px, -6px);
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
  white-space: nowrap;
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
  white-space: nowrap;
}

.masthead__logout-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  font-size: 13px;
  padding: 6px 12px;
  border-radius: 6px;
  max-width: 140px;
  margin-left: auto;
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

/* Tablet styles */
@media (max-width: 1024px) {
  .masthead__container {
    padding: 0 16px;
  }

  .masthead__nav {
    gap: 12px;
  }

  .masthead__nav-link {
    padding: 8px 14px;
    font-size: 13px;
  }

  .masthead__user {
    padding: 6px 14px;
  }
}

/* Mobile styles */
@media (max-width: 768px) {
  .masthead__container {
    padding: 0 16px;
    height: 60px;
  }

  .masthead__title {
    font-size: 20px;
  }

  .masthead__title i {
    font-size: 24px;
  }

  .masthead__mobile-toggle {
    display: flex;
  }

  .masthead__nav {
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    background: var(--gradient-primary);
    flex-direction: column;
    padding: 20px;
    gap: 16px;
    transform: translateY(-100%);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0, 53, 128, 0.3);
    backdrop-filter: blur(10px);
  }

  .masthead__nav--mobile-open {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
  }

  .masthead__nav-link {
    padding: 12px 16px;
    font-size: 16px;
    justify-content: flex-start;
    width: 100%;
    border-radius: 12px;
  }

  .masthead__user {
    flex-direction: column;
    gap: 12px;
    padding: 16px;
    border-radius: 12px;
    width: 100%;
  }

  .masthead__user-name {
    font-size: 16px;
    text-align: center;
  }

  .masthead__logout-btn {
    padding: 10px 16px;
    font-size: 14px;
    width: 100%;
    justify-content: center;
    border-radius: 8px;
  }
}

/* Small mobile styles */
@media (max-width: 480px) {
  .masthead__container {
    padding: 0 12px;
    height: 56px;
  }

  .masthead__title {
    font-size: 18px;
  }

  .masthead__title i {
    font-size: 20px;
  }

  .masthead__nav {
    top: 56px;
    padding: 16px;
  }

  .masthead__nav-link {
    padding: 10px 12px;
    font-size: 15px;
  }

  .masthead__user {
    padding: 12px;
  }

  .masthead__user-name {
    font-size: 15px;
  }

  .masthead__logout-btn {
    padding: 8px 12px;
    font-size: 13px;
  }
}

/* Very small mobile styles */
@media (max-width: 360px) {
  .masthead__title-text {
    font-size: 16px;
  }

  .masthead__nav-link {
    font-size: 14px;
  }

  .masthead__nav-text,
  .masthead__logout-text {
    display: none;
  }

  .masthead__nav-link,
  .masthead__logout-btn {
    justify-content: center;
    min-width: 44px;
  }
}

/* Landscape phone styles */
@media (max-width: 768px) and (orientation: landscape) {
  .masthead__container {
    height: 50px;
  }

  .masthead__nav {
    top: 50px;
    padding: 12px;
    gap: 12px;
  }

  .masthead__nav-link {
    padding: 8px 12px;
    font-size: 14px;
  }

  .masthead__user {
    padding: 8px 12px;
    flex-direction: row;
    gap: 8px;
  }

  .masthead__user-name {
    font-size: 14px;
  }

  .masthead__logout-btn {
    padding: 6px 10px;
    font-size: 12px;
  }
}
</style>
