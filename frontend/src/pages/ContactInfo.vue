<template>
  <div class="contact-info">
    <!-- Progress Indicator -->
    <div class="progress-bar">
      <div class="container">
        <div class="progress-steps">
          <div class="step completed">
            <div class="step-number">1</div>
            <span>SEARCH</span>
          </div>
          <div class="step completed">
            <div class="step-number">2</div>
            <span>SELECT ROOM</span>
          </div>
          <div class="step active">
            <div class="step-number">3</div>
            <span>CONTACT INFORMATION</span>
          </div>
          <div class="step">
            <div class="step-number">4</div>
            <span>CONFIRMATION</span>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="contact-layout">
        <!-- Left Column - Contact Form -->
        <div class="contact-form-section">
          <h2>CONTACT INFORMATION</h2>
          
          <form @submit.prevent="submitContactInfo" class="contact-form">
            <div class="form-group">
              <label for="title">TITLE</label>
              <select id="title" v-model="form.title" class="form-input">
                <option value="Mr.">Mr.</option>
                <option value="Mrs.">Mrs.</option>
                <option value="Ms.">Ms.</option>
                <option value="Dr.">Dr.</option>
              </select>
            </div>

            <div class="form-group">
              <label for="firstName">NAME</label>
              <input
                type="text"
                id="firstName"
                v-model="form.firstName"
                required
                class="form-input"
                :class="{ 'error': errors.firstName }"
              />
              <span v-if="errors.firstName" class="error-message">{{ errors.firstName }}</span>
            </div>

            <div class="form-group">
              <label for="email">EMAIL ADDRESS</label>
              <input
                type="email"
                id="email"
                v-model="form.email"
                required
                class="form-input"
                :class="{ 'error': errors.email }"
              />
              <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
            </div>

            <div class="form-actions">
              <button type="submit" class="button button--primary proceed-btn" :disabled="loading">
                {{ loading ? 'PROCESSING...' : 'PROCEED' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Right Column - Booking Summary -->
        <div class="booking-summary-section">
          <div class="booking-dates">
            <div class="date-info">
              {{ formatDate(bookingStore.checkInDate) }} — {{ formatDate(bookingStore.checkOutDate) }}
            </div>
            <div class="night-info">{{ numberOfNights }} NIGHT{{ numberOfNights > 1 ? 'S' : '' }}</div>
            <div class="guest-info">ROOM: {{ bookingStore.guestCount }} GUEST{{ bookingStore.guestCount > 1 ? 'S' : '' }}</div>
          </div>

          <div class="room-preview" v-if="selectedRoom">
            <img :src="selectedRoom.image" :alt="selectedRoom.name || selectedRoom.title" class="room-image" />
            <div class="room-info">
              <h3 class="room-name">{{ selectedRoom.name || selectedRoom.title }}</h3>
            </div>
          </div>

          <div class="price-breakdown">
            <div class="price-item">
              <span>Room</span>
              <span>S${{ roomPrice }}</span>
            </div>
            <div class="price-item">
              <span>Tax & Service Charges (9%)</span>
              <span>S${{ taxAmount }}</span>
            </div>
            <div class="price-total">
              <span><strong>TOTAL</strong></span>
              <span><strong>S${{ totalAmount }}</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingStore } from '../stores'

export default {
  name: 'ContactInfo',
  setup() {
    const router = useRouter()
    const bookingStore = useBookingStore()

    const loading = ref(false)
    const errors = ref({})

    const form = reactive({
      title: 'Mr.',
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      address: '',
      city: '',
      state: '',
      zipCode: '',
      specialRequests: ''
    })

    const selectedRoom = computed(() => bookingStore.selectedRoom)

    const numberOfNights = computed(() => {
      if (!bookingStore.checkInDate || !bookingStore.checkOutDate) return 0
      const checkin = new Date(bookingStore.checkInDate)
      const checkout = new Date(bookingStore.checkOutDate)
      return Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24))
    })

    const roomPrice = computed(() => {
      if (!selectedRoom.value) return 0
      return (selectedRoom.value.price * numberOfNights.value).toFixed(2)
    })

    const taxAmount = computed(() => {
      return (roomPrice.value * 0.09).toFixed(2)
    })

    const totalAmount = computed(() => {
      return (parseFloat(roomPrice.value) + parseFloat(taxAmount.value)).toFixed(2)
    })

    const formatDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    }

    const validateForm = () => {
      errors.value = {}

      if (!form.firstName.trim()) {
        errors.value.firstName = 'Name is required'
      }

      if (!form.email.trim()) {
        errors.value.email = 'Email is required'
      } else if (!/\S+@\S+\.\S+/.test(form.email)) {
        errors.value.email = 'Please enter a valid email'
      }

      return Object.keys(errors.value).length === 0
    }

    const submitContactInfo = async () => {
      if (!validateForm()) {
        return
      }

      loading.value = true

      try {
        bookingStore.setContactInfo(form)
        router.push('/confirmation')
      } catch (error) {
        console.error('Error saving contact info:', error)
      } finally {
        loading.value = false
      }
    }

    // Check if we have the required data, if not redirect to search
    onMounted(() => {
      if (!bookingStore.selectedRoom || !bookingStore.checkInDate || !bookingStore.checkOutDate) {
        router.push('/search')
      }
    })

    return {
      bookingStore,
      loading,
      errors,
      form,
      selectedRoom,
      numberOfNights,
      roomPrice,
      taxAmount,
      totalAmount,
      formatDate,
      validateForm,
      submitContactInfo
    }
  }
}
</script>

<style scoped>
.contact-info {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.progress-bar {
  background: white;
  border-bottom: 1px solid #e9ecef;
  padding: 20px 0;
}

.progress-steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  background: #e9ecef;
  color: #6c757d;
}

.step.completed .step-number {
  background: #333;
  color: white;
}

.step.active .step-number {
  background: #333;
  color: white;
}

.step span {
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
  text-align: center;
}

.step.active span {
  color: #333;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.contact-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  padding: 40px 0;
}

.contact-form-section {
  background: #e9ecef;
  padding: 40px;
  border-radius: 8px;
}

.contact-form-section h2 {
  font-size: 24px;
  font-weight: 700;
  color: #333;
  margin: 0 0 32px 0;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  font-size: 14px;
}

.form-input {
  padding: 12px 16px;
  border: 2px solid #dee2e6;
  border-radius: 4px;
  font-size: 16px;
  transition: all 0.2s ease;
  background: white;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error {
  border-color: #ff6b6b;
}

.error-message {
  color: #ff6b6b;
  font-size: 14px;
  margin-top: 6px;
}

.form-actions {
  margin-top: 16px;
}

.proceed-btn {
  width: 100%;
  padding: 16px;
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.booking-summary-section {
  background: white;
  padding: 0;
  border-radius: 8px;
  overflow: hidden;
  height: fit-content;
}

.booking-dates {
  background: #e9ecef;
  padding: 24px;
  text-align: center;
  border-bottom: 1px solid #e9ecef;
}

.date-info {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

.night-info {
  font-size: 14px;
  color: #6c757d;
  margin-bottom: 4px;
}

.guest-info {
  font-size: 14px;
  color: #6c757d;
}

.room-preview {
  padding: 24px;
  border-bottom: 1px solid #e9ecef;
}

.room-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 16px;
}

.room-name {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin: 0;
  text-transform: uppercase;
}

.price-breakdown {
  padding: 24px;
}

.price-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  color: #6c757d;
  font-size: 14px;
}

.price-total {
  display: flex;
  justify-content: space-between;
  padding: 16px 0 0 0;
  border-top: 1px solid #e9ecef;
  margin-top: 8px;
  font-size: 16px;
  color: #333;
}

@media (max-width: 768px) {
  .progress-steps {
    gap: 20px;
  }

  .step span {
    font-size: 10px;
  }

  .contact-layout {
    grid-template-columns: 1fr;
    gap: 24px;
    padding: 24px 0;
  }

  .contact-form-section {
    padding: 24px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 16px;
  }

  .progress-steps {
    gap: 16px;
  }

  .step-number {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }

  .contact-form-section {
    padding: 20px;
  }

  .contact-form-section h2 {
    font-size: 20px;
  }
}
</style>
