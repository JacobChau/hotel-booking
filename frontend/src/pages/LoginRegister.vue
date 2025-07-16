<template>
  <div class="auth-page">
    <div 
      class="auth-container" 
      v-motion
      :initial="{ opacity: 0, y: 50 }"
      :enter="{ opacity: 1, y: 0 }"
      :transition="{ duration: 600, type: 'spring' }"
    >
      <div 
        class="auth-card" 
        v-motion
        :initial="{ opacity: 0, scale: 0.9 }"
        :enter="{ opacity: 1, scale: 1 }"
        :transition="{ duration: 500, delay: 200, type: 'spring' }"
      >
        <div class="auth-tabs">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            :class="['auth-tab', { active: activeTab === tab.value }]"
            @click="switchTab(tab.value)"
          >
            <i :class="tab.icon"></i>
            {{ tab.label }}
            <div 
              v-if="activeTab === tab.value" 
              class="tab-indicator" 
              v-motion
              :initial="{ scaleX: 0 }"
              :enter="{ scaleX: 1 }"
              :transition="{ duration: 300, type: 'spring' }"
            ></div>
          </button>
        </div>

        <div class="form-container">
          <div v-if="successMessage" class="alert alert--success" 
               v-motion
               :initial="{ opacity: 0, y: -20 }"
               :enter="{ opacity: 1, y: 0 }"
               :transition="{ duration: 300 }"
          >
            <i class="pi pi-check-circle"></i>
            {{ successMessage }}
          </div>

          <!-- Error Message -->
          <div 
            v-if="errors.general" 
            class="alert alert--error" 
            v-motion
            :initial="{ opacity: 0, x: -10 }"
            :enter="{ opacity: 1, x: 0 }"
            :leave="{ opacity: 0, x: 10 }"
            :transition="{ duration: 300, type: 'spring' }"
          >
            <i class="pi pi-exclamation-triangle"></i>
            {{ errors.general }}
          </div>

          <div class="forms-wrapper">
            <AuthForm
              v-if="activeTab === 'login'"
              title="Welcome Back"
              subtitle="Please sign in to your account"
              @submit="handleLogin"
              key="login-form"
              v-motion
              :initial="{ opacity: 0, x: -30 }"
              :enter="{ opacity: 1, x: 0 }"
              :leave="{ opacity: 0, x: 30 }"
              :transition="{ duration: 400, type: 'spring' }"
            >
              <AuthInput
                id="loginEmail"
                v-model="loginForm.email"
                type="email"
                label="Email Address"
                placeholder="Enter your email"
                icon="pi pi-envelope"
                :error="errors.email"
                autocomplete="email"
                required
                :delay="100"
              />

              <AuthInput
                id="loginPassword"
                v-model="loginForm.password"
                type="password"
                label="Password"
                placeholder="Enter your password"
                icon="pi pi-lock"
                :error="errors.password"
                autocomplete="current-password"
                required
                :delay="200"
              />

              <div 
                class="form-options" 
                v-motion
                :initial="{ opacity: 0, x: -30 }"
                :enter="{ opacity: 1, x: 0 }"
                :transition="{ duration: 400, delay: 300, type: 'spring' }"
              >
                <label class="checkbox-wrapper">
                  <input type="checkbox" v-model="loginForm.remember" />
                  <span class="checkbox-custom"></span>
                  <span class="checkbox-label">Remember me</span>
                </label>
                <button
                  type="button"
                  @click="showForgotPassword = true"
                  class="forgot-link"
                >
                  Forgot Password?
                </button>
              </div>

              <AuthButton
                  text="Sign In"
                type="submit"
                variant="primary"
                :loading="loading"
                :disabled="loading"
                :delay="100"
              >
                <template #icon>
                  <i class="pi pi-sign-in"></i>
                </template>
                {{ loading ? 'Signing in...' : 'Sign In' }}
              </AuthButton>
            </AuthForm>

            <AuthForm
              v-else-if="activeTab === 'register'"
              title="Create Account"
              subtitle="Join us and start your journey"
              @submit="handleRegister"
              key="register-form"
              v-motion
              :initial="{ opacity: 0, x: 30 }"
              :enter="{ opacity: 1, x: 0 }"
              :leave="{ opacity: 0, x: -30 }"
              :transition="{ duration: 400, type: 'spring' }"
            >
              <AuthInput
                id="registerName"
                v-model="registerForm.name"
                type="text"
                label="Full Name"
                placeholder="Enter your full name"
                icon="pi pi-user"
                :error="errors.name"
                autocomplete="name"
                required
                :delay="100"
              />

              <AuthInput
                id="registerEmail"
                v-model="registerForm.email"
                type="email"
                label="Email Address"
                placeholder="Enter your email"
                icon="pi pi-envelope"
                :error="errors.email"
                autocomplete="email"
                required
                :delay="200"
              />

              <AuthInput
                id="registerPassword"
                v-model="registerForm.password"
                type="password"
                label="Password"
                placeholder="Create password"
                icon="pi pi-lock"
                :error="errors.password"
                autocomplete="new-password"
                required
                :delay="300"
              >
                <template #after>
                  <PasswordStrength
                    v-if="registerForm.password"
                    :password="registerForm.password"
                  />
                </template>
              </AuthInput>

              <AuthInput
                id="confirmPassword"
                v-model="registerForm.confirmPassword"
                type="password"
                label="Confirm Password"
                placeholder="Confirm your password"
                icon="pi pi-lock"
                :error="errors.confirmPassword"
                autocomplete="new-password"
                required
                :delay="400"
              />

              <div 
                class="form-group" 
                v-motion
                :initial="{ opacity: 0, x: -30 }"
                :enter="{ opacity: 1, x: 0 }"
                :transition="{ duration: 400, delay: 500, type: 'spring' }"
              >
                <label class="checkbox-wrapper">
                  <input type="checkbox" v-model="registerForm.agreeTerms" />
                  <span class="checkbox-custom"></span>
                  <span class="checkbox-label">
                    I agree to the <a href="#" class="terms-link">Terms of Service</a> and 
                    <a href="#" class="terms-link">Privacy Policy</a>
                  </span>
                </label>
                <span v-if="errors.agreeTerms" class="error-message">{{ errors.agreeTerms }}</span>
              </div>

              <AuthButton
                  text="Create Account"
                  type="submit"
                  variant="primary"
                  :loading="loading"
                  :disabled="loading"
                  :delay="100"
              >
                <template #icon>
                  <i class="pi pi-user-plus"></i>
                </template>
                {{ loading ? 'Creating Account...' : 'Create Account' }}
              </AuthButton>
            </AuthForm>
          </div>
        </div>
      </div>
    </div>

    <!-- Forgot Password Modal -->
    <div v-if="showForgotPassword" class="modal-overlay" @click="showForgotPassword = false">
      <div 
        class="modal-content" 
        @click.stop
        v-motion
        :initial="{ opacity: 0, scale: 0.9 }"
        :enter="{ opacity: 1, scale: 1 }"
        :leave="{ opacity: 0, scale: 0.9 }"
        :transition="{ duration: 300, type: 'spring' }"
      >
        <div class="modal-header">
          <h3>Reset Password</h3>
          <button @click="showForgotPassword = false" class="modal-close">
            <i class="pi pi-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Enter your email address and we'll send you a link to reset your password.</p>
          <AuthInput
            id="forgotEmail"
            v-model="forgotPasswordEmail"
            type="email"
            label="Email Address"
            placeholder="Enter your email"
            icon="pi pi-envelope"
            :error="errors.forgotPassword"
            required
          />
        </div>
        <div class="modal-footer">
          <AuthButton
              text="Cancel"
            variant="outline"
            @click="showForgotPassword = false"
          >
            Cancel
          </AuthButton>
          <AuthButton
              text="Send Reset Link"
            variant="primary"
            @click="handleForgotPassword"
          >
            Send Reset Link
          </AuthButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AuthForm from '../components/auth/AuthForm.vue'
import AuthInput from '../components/auth/AuthInput.vue'
import AuthButton from '../components/auth/AuthButton.vue'
import PasswordStrength from '../components/auth/PasswordStrength.vue'

export default {
  name: 'LoginRegister',
  components: {
    AuthForm,
    AuthInput,
    AuthButton,
    PasswordStrength
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    const activeTab = ref('login')
    const loading = ref(false)
    const showForgotPassword = ref(false)
    const forgotPasswordEmail = ref('')
    const successMessage = ref('')
    const errors = ref({})

    const loginForm = reactive({
      email: '',
      password: '',
      remember: false
    })

    const registerForm = reactive({
      name: '',
      email: '',
      password: '',
      confirmPassword: '',
      agreeTerms: false
    })

    const tabs = [
      { value: 'login', label: 'LOGIN', icon: 'pi pi-sign-in' },
      { value: 'register', label: 'REGISTER', icon: 'pi pi-user-plus' }
    ]

    const switchTab = (tab) => {
      activeTab.value = tab
      errors.value = {}
      successMessage.value = ''
      
      Object.assign(loginForm, { email: '', password: '', remember: false })
      Object.assign(registerForm, {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        agreeTerms: false
      })
    }

    const validateEmail = (email) => {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    }

    const validateLogin = () => {
      const newErrors = {}

      if (!loginForm.email) {
        newErrors.email = 'Email is required'
      } else if (!validateEmail(loginForm.email)) {
        newErrors.email = 'Please enter a valid email address'
      }

      if (!loginForm.password) {
        newErrors.password = 'Password is required'
      }

      errors.value = newErrors
      return Object.keys(newErrors).length === 0
    }

    const validateRegister = () => {
      const newErrors = {}

      if (!registerForm.name.trim()) {
        newErrors.name = 'Full name is required'
      }

      if (!registerForm.email) {
        newErrors.email = 'Email is required'
      } else if (!validateEmail(registerForm.email)) {
        newErrors.email = 'Please enter a valid email address'
      }

      if (!registerForm.password) {
        newErrors.password = 'Password is required'
      } else if (registerForm.password.length < 8) {
        newErrors.password = 'Password must be at least 8 characters long'
      }

      if (registerForm.password !== registerForm.confirmPassword) {
        newErrors.confirmPassword = 'Passwords do not match'
      }

      if (!registerForm.agreeTerms) {
        newErrors.agreeTerms = 'You must agree to the terms and conditions'
      }

      errors.value = newErrors
      return Object.keys(newErrors).length === 0
    }

    const handleLogin = async () => {
      if (!validateLogin()) return

      loading.value = true
      errors.value = {}

      try {
        const result = await authStore.login(loginForm)
        successMessage.value = 'Login successful! Redirecting...'
        
        setTimeout(() => {
          if (result.redirectPath) {
            router.push(result.redirectPath)
          } else {
            router.push('/dashboard')
          }
        }, 1000)
      } catch (error) {
        errors.value = { general: error.message || 'Login failed. Please check your credentials.' }
      } finally {
        loading.value = false
      }
    }

    const handleRegister = async () => {
      if (!validateRegister()) return

      loading.value = true
      errors.value = {}

      try {
        const userData = {
          name: registerForm.name,
          email: registerForm.email,
          password: registerForm.password
        }
        
        const result = await authStore.register(userData)
        successMessage.value = 'Account created successfully! Please check your email to verify your account.'
        
        setTimeout(() => {
          if (result.redirectPath) {
            router.push(result.redirectPath)
          } else {
            router.push('/dashboard')
          }
        }, 2000)
      } catch (error) {
        errors.value = { general: error.message || 'Registration failed. Please try again.' }
      } finally {
        loading.value = false
      }
    }

    const handleForgotPassword = async () => {
      if (!forgotPasswordEmail.value || !validateEmail(forgotPasswordEmail.value)) {
        errors.value = { forgotPassword: 'Please enter a valid email address' }
        return
      }

      try {
        await new Promise(resolve => setTimeout(resolve, 1000))
        successMessage.value = 'Password reset link sent to your email!'
        showForgotPassword.value = false
        forgotPasswordEmail.value = ''
        errors.value = {}
      } catch (error) {
        errors.value = { forgotPassword: 'Failed to send reset email. Please try again.' }
      }
    }

    onMounted(() => {
      authStore.clearError()
    })

    return {
      activeTab,
      loading,
      showForgotPassword,
      forgotPasswordEmail,
      successMessage,
      errors,
      loginForm,
      registerForm,
      tabs,
      switchTab,
      handleLogin,
      handleRegister,
      handleForgotPassword
    }
  }
}
</script>

<style scoped>
:global(.main-content) {
  padding-top: 0 !important;
}

.auth-page {
  min-height: calc(100vh - 70px);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: var(--spacing-xl);
  background: var(--gray-200);
  overflow-y: auto;
}

.background-container {
  position: absolute;
  inset: 0;
  overflow: hidden;
  z-index: 0;
}

.floating-element {
  position: absolute;
  width: 20rem;
  height: 20rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  filter: blur(3rem);
}

.floating-element-1 {
  top: -10rem;
  right: -10rem;
}

.floating-element-2 {
  bottom: -10rem;
  left: -10rem;
}

.gradient-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
  z-index: 1;
}

.auth-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 28rem;
}


.auth-card {
  background: var(--white);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-xl);
}

/* Tab Navigation */
.auth-tabs {
  display: flex;
  background: rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xs);
  margin-bottom: var(--spacing-md);
}

.auth-tab {
  flex: 1;
  position: relative;
  background: var(--hotel-blue-light);
  border: none;
  color: var(--white);
  padding: var(--spacing-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  font-weight: 500;
  opacity: 0.8;
}

.auth-tab:first-child {
  border-top-left-radius: var(--radius-lg);
}

.auth-tab:last-child {
  border-top-right-radius: var(--radius-lg);
}

.auth-tab:hover {
  color: white;
  opacity: 1;
  background: var(--primary-blue-dark);
}

.auth-tab.active {
  color: var(--white);
  opacity: 1;
  background: var(--hotel-blue);
  box-shadow: var(--shadow-sm);
}

.tab-indicator {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--gradient-primary);
  border-radius: 1px;
}

.form-container {
  position: relative;
}

.forms-wrapper {
  min-height: 400px;
  position: relative;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

/* Form Options */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-lg);
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  cursor: pointer;
  margin-bottom: var(--spacing-sm)  ;
}

.checkbox-wrapper input[type="checkbox"] {
  display: none;
}

.checkbox-custom {
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid rgba(0, 0, 0, 0.3);
  border-radius: var(--radius-sm);
  position: relative;
  transition: all var(--transition-fast);
}

.checkbox-wrapper input[type="checkbox"]:checked + .checkbox-custom {
  background: var(--gradient-primary);
  border-color: var(--primary-blue);
}

.checkbox-wrapper input[type="checkbox"]:checked + .checkbox-custom::after {
  content: '';
  position: absolute;
  left: 0.25rem;
  top: 0.125rem;
  width: 0.375rem;
  height: 0.625rem;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.checkbox-label {
  color: var(--gray-900);
  font-size: 0.875rem;
}

.forgot-link {
  background: none;
  border: none;
  color: var(--primary-blue);
  cursor: pointer;
  font-size: 0.875rem;
  text-decoration: none;
  transition: color var(--transition-fast);
}

.forgot-link:hover {
  color: var(--primary-purple);
}

.terms-link {
  color: var(--primary-blue);
  text-decoration: none;
  transition: color var(--transition-fast);
}

.terms-link:hover {
  color: var(--primary-purple);
}


.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--spacing-lg);
}

.modal-content {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: var(--spacing-xl);
  width: 100%;
  max-width: 28rem;
  box-shadow: var(--shadow-xl);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-lg);
}

.modal-header h3 {
  color: white;
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  padding: var(--spacing-sm);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.modal-close:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.modal-body {
  margin-bottom: var(--spacing-lg);
}

.modal-body p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: var(--spacing-lg);
}

.modal-footer {
  display: flex;
  gap: var(--spacing-md);
  justify-content: flex-end;
}

@media (max-width: 768px) {
  :global(.main-content) {
    padding-top: 0 !important;
  }
  
  .auth-page {
    padding: var(--spacing-lg);
  }

  .form-options {
    flex-direction: column;
    gap: var(--spacing-md);
    align-items: flex-start;
  }

  .modal-footer {
    flex-direction: column;
  }
}
</style>
