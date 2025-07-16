<template>
  <button
    :type="type"
    :class="buttonClasses"
    :disabled="disabled"
    v-motion
    :initial="{ opacity: 0, x: -30 }"
    :enter="{ opacity: 1, x: 0 }"
    :transition="{ duration: 400, delay: delay || 0, type: 'spring' }"
    :delay="delay"
    v-bind="$attrs"
  >
    <span v-if="loading" class="loading-spinner"></span>
    <i v-else-if="icon" :class="icon"></i>
    <span class="button-text">{{ loading ? loadingText : text }}</span>
  </button>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'AuthButton',
  inheritAttrs: false,
  props: {
    type: {
      type: String,
      default: 'button'
    },
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'outline'].includes(value)
    },
    text: {
      type: String,
      required: true
    },
    loadingText: {
      type: String,
      default: 'Loading...'
    },
    icon: {
      type: String,
      default: ''
    },
    loading: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    delay: {
      type: Number,
      default: 0
    }
  },
  setup(props) {
    const buttonClasses = computed(() => [
      'auth-button',
      `auth-button-${props.variant}`,
      {
        'auth-button-loading': props.loading
      }
    ])
    
    return {
      buttonClasses
    }
  }
}
</script>

<style scoped>
.auth-button {
  width: 100%;
  padding: var(--spacing-md) var(--spacing-lg);
  border: none;
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: var(--transition-normal);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  position: relative;
  overflow: hidden;
}

.auth-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

.auth-button-primary {
  background: var(--hotel-blue);
  color: var(--white);
}

.auth-button-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  background: var(--hotel-blue-light);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}


.auth-button-secondary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
}

.auth-button-outline {
  background: var(--glass-bg);
  color: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--glass-border);
}

.auth-button-outline:hover:not(:disabled) {
  background: var(--glass-bg-strong);
  color: var(--white);
}

.loading-spinner {
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid var(--white);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.button-text {
  font-weight: inherit;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style> 