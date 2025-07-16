<template>
  <div class="confirmation">
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
          <div class="step completed">
            <div class="step-number">3</div>
            <span>CONTACT INFORMATION</span>
          </div>
          <div class="step active">
            <div class="step-number">4</div>
            <span>CONFIRMATION</span>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-section">
        <div class="loading-content">
          <div class="loading-spinner"></div>
          <h2>Processing your booking...</h2>
          <p>Please wait while we confirm your reservation.</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-section">
        <div class="error-content">
          <h2>Booking Failed</h2>
          <p>{{ error }}</p>
          <button @click="retryBooking" class="button button--primary">Try Again</button>
          <button @click="goBack" class="button button--secondary">Go Back</button>
        </div>
      </div>

      <!-- Success State -->
      <div v-else-if="bookingConfirmed" class="confirmation-content">
        <div class="success-header">
          <div class="success-icon"><i class="pi pi-check-circle"></i></div>
          <h1>YOUR BOOKING HAS BEEN CONFIRMED</h1>
          <p>We have sent your booking confirmation to {{ bookingStore.contactInfo.email }}</p>
          
          <!-- Key Booking Info -->
          <div class="booking-summary">
            <div class="summary-item">
              <span class="summary-label">Check-in/Check-out:</span>
              <span class="summary-value">{{ formatDate(bookingStore.checkInDate) }} — {{ formatDate(bookingStore.checkOutDate) }}</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Booking Confirmation Number:</span>
              <span class="summary-value">{{ confirmationNumber }}</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Total Price for {{ numberOfNights }} Night{{ numberOfNights > 1 ? 's' : '' }}:</span>
              <span class="summary-value">S${{ totalAmount }}</span>
            </div>
          </div>
        </div>

        <div class="booking-details-section">
          <div class="booking-main-details">
            <!-- Room Image and Info -->
            <div class="room-section">
              <div class="room-image-container">
                <img 
                  :src="bookingStore.selectedRoom?.image || '/api/placeholder/280/200'" 
                  :alt="bookingStore.selectedRoom?.name || 'Room'" 
                  class="room-image"
                />
              </div>
              <div class="room-info">
                <h3 class="room-title">ROOM: {{ (bookingStore.selectedRoom?.name || bookingStore.selectedRoom?.title || 'JUNIOR SUITE').toUpperCase() }}</h3>
                <p class="guest-count">{{ bookingStore.guestCount }} Guest{{ bookingStore.guestCount > 1 ? 's' : '' }}</p>
              </div>
            </div>

            <!-- Guest Details -->
            <div class="guest-section">
              <h3 class="section-title">GUEST DETAILS</h3>
              <div class="guest-details">
                <div class="detail-row">
                  <span class="detail-label">Name:</span>
                  <span class="detail-value">{{ bookingStore.contactInfo.title }} {{ bookingStore.contactInfo.firstName }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Email Address:</span>
                  <span class="detail-value">{{ bookingStore.contactInfo.email }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Package Breakdown -->
          <div class="package-section">
            <h3 class="section-title">PACKAGE:</h3>
            <div class="package-breakdown">
              <div class="package-item">
                <span class="package-label">Room</span>
                <span class="package-value">S${{ roomPrice }}</span>
              </div>
              <div class="package-item">
                <span class="package-label">Tax & Service Charges (9%)</span>
                <span class="package-value">S${{ taxAmount }}</span>
              </div>
              <div class="package-divider"></div>
              <div class="package-total">
                <span class="total-label">Total Price</span>
                <span class="total-value">S${{ totalAmount }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="action-buttons">
          <button @click="goToDashboard" class="button button--primary">Go to Dashboard</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingStore } from '../stores'
import { bookingsAPI } from '../api/bookings'

export default {
  name: 'Confirmation',
  setup() {
    const router = useRouter()
    const bookingStore = useBookingStore()

    const loading = ref(false)
    const error = ref('')
    const bookingConfirmed = ref(false)
    const confirmationNumber = ref('')

    const numberOfNights = computed(() => {
      if (!bookingStore.checkInDate || !bookingStore.checkOutDate) return 0
      const checkin = new Date(bookingStore.checkInDate)
      const checkout = new Date(bookingStore.checkOutDate)
      return Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24))
    })

    const roomPrice = computed(() => {
      if (!bookingStore.selectedRoom) return 0
      return (bookingStore.selectedRoom.price * numberOfNights.value).toFixed(2)
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

    const generateBookingNumber = () => {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
      let result = ''
      for (let i = 0; i < 10; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length))
      }
      return result
    }

    const createBooking = async () => {
      loading.value = true
      error.value = ''

      try {
        const bookingData = {
          room_id: bookingStore.selectedRoom.id,
          check_in: bookingStore.checkInDate,
          check_out: bookingStore.checkOutDate,
          guests: bookingStore.guestCount,
          booking_number: generateBookingNumber(),
          total: totalAmount.value,
          status: 'confirmed',
          nights: numberOfNights.value,
          title: bookingStore.contactInfo.title,
          name: bookingStore.contactInfo.firstName,
          email: bookingStore.contactInfo.email
        }

        const response = await bookingsAPI.createBooking(bookingData)
        
        if (response.success || response.data) {
          confirmationNumber.value = bookingData.booking_number
          bookingConfirmed.value = true
          
          bookingStore.currentStep = 4
        } else {
          throw new Error('Failed to create booking')
        }
      } catch (err) {
        console.error('Booking creation error:', err)
        error.value = err.response?.data?.message || 'Failed to create booking. Please try again.'
      } finally {
        loading.value = false
      }
    }

    const retryBooking = () => {
      createBooking()
    }

    const goBack = () => {
      router.push('/contact-info')
    }

    const goToDashboard = () => {
      bookingStore.resetBooking()
      router.push('/dashboard')
    }

    const printConfirmation = () => {
      window.print()
    }


    onMounted(() => {
      if (!bookingStore.selectedRoom || !bookingStore.checkInDate || !bookingStore.checkOutDate || !bookingStore.contactInfo.email) {
        router.push('/search')
        return
      }

      createBooking()
    })

    return {
      bookingStore,
      loading,
      error,
      bookingConfirmed,
      confirmationNumber,
      numberOfNights,
      roomPrice,
      taxAmount,
      totalAmount,
      formatDate,
      createBooking,
      retryBooking,
      goBack,
      goToDashboard,
      printConfirmation
    }
  }
}
</script>

<style scoped>
.confirmation {
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

.step.completed .step-number,
.step.active .step-number {
  background: #333;
  color: white;
}

.step span {
  font-size: 16px;
  font-weight: 600;
  color: #6c757d;
  text-align: center;
}

.step.active span {
  color: #333;
  font-weight: 700;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 20px;
}

.confirmation-content {
  padding: 40px 0;
}

.success-header {
  text-align: center;
  margin-bottom: 40px;
}

.success-header h1 {
  font-size: 30px;
  font-weight: 700;
  color: #333;
}

.success-header p {
  color: #6c757d;
  font-size: 16px;
  margin: 0;
}

.booking-summary {
  background: white;
  padding: 24px 32px;
  border-radius: 8px;
  margin-top: 40px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
  border-bottom: none;
  font-weight: 600;
}

.summary-label {
  color: #6c757d;
  font-size: 14px;
  font-weight: 500;
}

.summary-value {
  color: #333;
  font-size: 14px;
  font-weight: 600;
}

.booking-details-section {
  background: white;
  padding: 32px;
  border-radius: 8px;
  margin-bottom: 32px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.07);
}

.booking-main-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  margin-bottom: 32px;
}

.room-section {
  background: transparent;
  padding: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: none;
}

.room-image-container {
  width: 100%;
  height: 200px;
  overflow: hidden;
  border-radius: 8px 8px 0 0;
  background: white;
}

.room-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.room-info {
  padding: 20px;
  background: #f5f7fa;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.room-title {
  font-size: 16px;
  font-weight: 700;
  color: #333;
  margin-bottom: 4px;
  letter-spacing: 0.5px;
}

.guest-count {
  font-size: 14px;
  color: #6c757d;
  margin: 0;
}

.guest-section {
  height: fit-content;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #333;
  margin: 0 0 12px 0;
  letter-spacing: 0.5px;
}

.guest-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.detail-label {
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
}

.detail-value {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin-bottom: 4px;
}

.package-section {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.package-breakdown {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.package-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.package-label {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
}

.package-value {
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.package-divider {
  height: 1px;
  background: #e9ecef;
  margin: 8px 0;
}

.package-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0 0 0;
  border-top: 2px solid #e9ecef;
}

.total-label {
  font-size: 16px;
  font-weight: 700;
  color: #333;
}

.total-value {
  font-size: 18px;
  font-weight: 700;
  color: #333;
}

.action-buttons {
  text-align: center;
}

.action-buttons .button {
  padding: 16px 32px;
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  padding: 32px;
  max-width: 400px;
  width: 90%;
  text-align: center;
}

.success-icon {
  width: 60px;
  height: 60px;
  background: #28a745;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin: 0 auto 20px;
}

.modal-content h3 {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin: 0 0 16px 0;
}

.modal-content p {
  color: #6c757d;
  margin: 0 0 8px 0;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 24px;
}

.modal-actions .button {
  padding: 10px 20px;
  font-size: 14px;
}

.loading-section {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

.loading-content {
  text-align: center;
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

.error-section {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

.error-content {
  text-align: center;
  padding: 20px;
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  border-radius: 8px;
}

.error-content h2 {
  font-size: 20px;
  margin-bottom: 10px;
}

.error-content p {
  font-size: 16px;
  margin-bottom: 20px;
}

.error-content .button {
  margin: 0 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.success-icon {
  font-size: 48px;
  margin-bottom: 20px;
}

@media (max-width: 768px) {
  .progress-steps {
    gap: 20px;
  }

  .step span {
    font-size: 10px;
  }

  .booking-details-section {
    padding: 20px;
  }
  
  .booking-main-details {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .room-image-container {
    height: 150px;
  }
  
  .detail-value {
    font-size: 14px;
  }
  
  .section-title {
    font-size: 14px;
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

  .confirmation-content {
    padding: 24px 0;
  }

  .success-header h1 {
    font-size: 24px;
  }

  .booking-details-section {
    padding: 20px;
  }

  .detail-card {
    padding: 20px;
  }
}
</style>
