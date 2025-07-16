<template>
  <div 
  class="form-group" 
  v-motion
  :initial="{ opacity: 0, x: -30 }"
  :enter="{ opacity: 1, x: 0 }"
  :transition="{ duration: 400, delay: delay || 0, type: 'spring' }"
>
    <label v-if="label" :for="id" class="form-label">{{ label }}</label>
    <div class="input-container">
      <i v-if="icon" :class="['input-icon', icon]"></i>
      <input
        :id="id"
        :type="computedType"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :required="required"
        :class="['form-input', { 'error': hasError }]"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        v-bind="$attrs"
      />
      <button
        v-if="type === 'password'"
        type="button"
        @click="togglePassword"
        class="password-toggle"
      >
        <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
      </button>
    </div>
    <span v-if="error" class="error-message">{{ error }}</span>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'AuthInput',
  inheritAttrs: false,
  emits: ['update:modelValue'],
  props: {
    id: {
      type: String,
      required: true
    },
    label: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    modelValue: {
      type: String,
      default: ''
    },
    icon: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    autocomplete: {
      type: String,
      default: ''
    },
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    delay: {
      type: Number,
      default: 0
    }
  },
  setup(props) {
    const showPassword = ref(false)
    
    const computedType = computed(() => {
      if (props.type === 'password') {
        return showPassword.value ? 'text' : 'password'
      }
      return props.type
    })
    
    const hasError = computed(() => Boolean(props.error))
    
    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }
    
    return {
      showPassword,
      computedType,
      hasError,
      togglePassword
    }
  }
}
</script>

<style scoped>
.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-lg);
}

.form-label {
  color: var(--gray-900);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: var(--spacing-md);
  color: var(--primary-blue);
  font-size: var(--font-size-xl);
  z-index: 2;
}

.form-input {
  width: 100%;
  padding: var(--spacing-medium) var(--spacing-lg) var(--spacing-medium) var(--spacing-2xl);
  background: var(--gray-100);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  color: var(--black);
  font-size: var(--font-size-md);
  transition: var(--transition-normal);
  backdrop-filter: var(--glass-blur-light);
}

.form-input::placeholder {
  color: rgba(0, 0, 0, 0.5);
}

.form-input:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.4);
  background: var(--gray-200);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.form-input.error {
  border-color: var(--error);
}

.password-toggle {
  position: absolute;
  right: var(--spacing-lg);
  background: none;
  border: none;
  color: var(--gray-600);
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: var(--transition-normal);
  z-index: 2;
  font-size: var(--font-size-lg);
}

.password-toggle:hover {
  color: var(--primary-blue);
  background: rgba(0, 113, 194, 0.1);
}

.password-toggle:focus {
  outline: none;
  color: var(--primary-blue);
  background: rgba(0, 113, 194, 0.1);
}

</style> 