<template>
  <div id="app">
    <Masthead />
    <main class="main-content">
      <router-view v-slot="{ Component, route }">
        <transition 
          :name="route.meta.transition || 'fade'"
          mode="out-in"
          @enter="onEnter"
          @leave="onLeave"
        >
          <component 
            :is="Component" 
            :key="route.path"
            v-motion
            :initial="{ opacity: 0, y: 30 }"
            :enter="{ opacity: 1, y: 0 }"
            :transition="{ duration: 400, delay: 100, type: 'spring' }"
          />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script>
import { onMounted } from 'vue'
import Masthead from './components/Masthead.vue'
import { useAuthStore } from './stores'

export default {
  name: 'App',
  components: {
    Masthead
  },
  setup() {
    const authStore = useAuthStore()

    onMounted(() => {
      // Initialize auth state on app start
      authStore.initAuth()
    })

    return {}
  },
  methods: {
    onEnter(el) {
      // Add entering animation class
      el.classList.add('page-enter')
    },
    onLeave(el) {
      // Add leaving animation class
      el.classList.add('page-leave')
    }
  }
}
</script>

<style>
/* Global App Styles */
#app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
    'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  min-height: 100vh;
  background: #f5f7fa;
}

.main-content {
  position: relative;
  z-index: 1;
}


@keyframes pageEnter {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pageLeave {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-10px);
  }
}

@keyframes loadingPulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}


@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    transform: translate3d(0, -10px, 0);
  }
  70% {
    animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    transform: translate3d(0, -5px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Responsive typography */
@media (max-width: 768px) {
  #app {
    font-size: 14px;
  }
}

</style>
