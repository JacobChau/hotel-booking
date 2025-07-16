<template>
  <div 
    v-if="password" 
    class="password-strength" 
    v-motion
    :initial="{ opacity: 0, y: -10 }"
    :enter="{ opacity: 1, y: 0 }"
    :transition="{ duration: 300, delay: 100 }"
  >
    <div class="strength-header">
      <span class="strength-label">Password strength</span>
      <span class="strength-text" :class="strengthClass">{{ strengthText }}</span>
    </div>
    <div class="strength-bar">
      <div
        class="strength-fill"
        :class="strengthClass"
        :style="{ width: strengthWidth }"
        v-motion
        :initial="{ scaleX: 0 }"
        :enter="{ scaleX: 1 }"
        :transition="{ duration: 500, delay: 200 }"
      ></div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'PasswordStrength',
  props: {
    password: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const validatePassword = (password) => {
      return {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
      }
    }

    const passwordStrength = computed(() => {
      if (!props.password) return { text: '', class: '', width: '0%' }

      const validations = validatePassword(props.password)
      const score = Object.values(validations).filter(Boolean).length

      if (score < 2) return { text: 'Weak', class: 'strength-weak', width: '25%' }
      if (score < 4) return { text: 'Medium', class: 'strength-medium', width: '50%' }
      if (score < 5) return { text: 'Good', class: 'strength-good', width: '75%' }
      return { text: 'Strong', class: 'strength-strong', width: '100%' }
    })

    const strengthText = computed(() => passwordStrength.value.text)
    const strengthClass = computed(() => passwordStrength.value.class)
    const strengthWidth = computed(() => passwordStrength.value.width)

    return {
      strengthText,
      strengthClass,
      strengthWidth
    }
  }
}
</script>

<style scoped>
.password-strength {
  margin-top: var(--spacing-sm);
}

.strength-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: var(--spacing-xs);
}

.strength-label,
.strength-text {
  font-size: var(--font-size-xs);
  color: rgba(255, 255, 255, 0.7);
}

.strength-text.strength-weak { color: var(--error); }
.strength-text.strength-medium { color: var(--warning); }
.strength-text.strength-good { color: var(--info); }
.strength-text.strength-strong { color: var(--success); }

.strength-bar {
  width: 100%;
  height: var(--spacing-sm);
  background: var(--glass-bg-strong);
  border-radius: var(--radius-sm);
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  border-radius: var(--radius-sm);
  transition: var(--transition-normal);
}

.strength-fill.strength-weak { background: var(--error); }
.strength-fill.strength-medium { background: var(--warning); }
.strength-fill.strength-good { background: var(--info); }
.strength-fill.strength-strong { background: var(--success); }
</style> 