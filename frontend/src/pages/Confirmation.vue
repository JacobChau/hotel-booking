<template>
  <div class="confirmation">
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
      <div v-if="loading" class="loading-section">
        <div class="loading-content">
          <div class="loading-spinner"></div>
          <h2>Processing your booking...</h2>
          <p>Please wait while we confirm your reservation.</p>
        </div>
      </div>

      <div v-else-if="error" class="error-section">
        <div class="error-content">
          <h2>Booking Failed</h2>
          <p>{{ error }}</p>
          <button @click="retryBooking" class="button button--primary">Try Again</button>
          <button @click="goBack" class="button button--secondary">Go Back</button>
        </div>
      </div>

      <div v-else-if="bookingConfirmed" class="confirmation-content">
        <div class="success-header">
          <div class="success-icon"><i class="pi pi-check-circle"></i></div>
          <h1>YOUR BOOKING HAS BEEN CONFIRMED</h1>
          <p>We have sent your booking confirmation to {{ bookingStore.contactInfo.email }}</p>
        </div>

        <div class="booking-details-section">
          <div class="combined-details">
            <!-- Left Column - Room and Guest Info -->
            <div class="info-column">
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

            <!-- Right Column - Booking Details and Price -->
            <div class="booking-summary-column">
              <div class="booking-dates-section">
                <h3 class="section-title">BOOKING DETAILS</h3>
                <div class="booking-dates">
                  <div class="date-info">
                    {{ formatDate(bookingStore.checkInDate) }} — {{ formatDate(bookingStore.checkOutDate) }}
                  </div>
                  <div class="night-info">{{ numberOfNights }} NIGHT{{ numberOfNights > 1 ? 'S' : '' }}</div>
                  <div class="confirmation-info">
                    <span class="confirmation-label">Confirmation Number:</span>
                    <span class="confirmation-number">{{ confirmationNumber }}</span>
                  </div>
                </div>
              </div>

              <div class="package-section">
                <h3 class="section-title">PACKAGE BREAKDOWN</h3>
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
import { useHead } from '@unhead/vue'
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

    useHead({
      title: 'Booking Confirmation - Hotel Booking',
      meta: [
        {
          name: 'description',
          content: 'Confirm your hotel booking details and complete your reservation. Review dates, room selection, and total cost.'
        }
      ]
    })

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

  .confirmation-content {
    padding: 40px 0;
  }

  .success-header {
    text-align: center;
    margin-bottom: 40px;
  }

  .success-header h1 {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
    color: var(--gray-900);
  }

  .success-header p {
    color: var(--gray-700);
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-2xl);
  }

  .booking-details-section {
    background: var(--white);
    padding: 32px;
    border-radius: 8px;
    margin-bottom: var(--spacing-2xl);
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
  }

  .combined-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
  }

  .info-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .booking-summary-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
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
    background: var(--white);
  }

  .room-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .room-info {
    padding: var(--spacing-md);
    background: var(--gray-200);
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  .room-title {
    font-size: var(--font-size-md);
    font-weight: var(--font-weight-bold);
    color: var(--gray-900);
    letter-spacing: 0.5px;
  }

  .guest-count {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    margin: 0;
  }

  .guest-section {
    background: var(--gray-200);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  .booking-dates-section {
    background: var(--gray-200);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  .date-info {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: #333;
    margin-bottom: var(--spacing-xs);
  }

  .night-info {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    margin-bottom: 12px;
  }

  .confirmation-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
  }

  .confirmation-label {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    color: var(--gray-600);
    text-transform: uppercase;
  }

  .confirmation-number {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--gray-900);
    letter-spacing: 1px;
  }

  .section-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--primary-blue);
    margin: 0 0 var(--spacing-md) 0;
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
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--gray-600);
  }

  .detail-value {
    font-size: var(--font-size-md);
    font-weight: var(--font-weight-semibold);
    color: var(--gray-900);
    margin-bottom: 4px;
  }

  .package-section {
    background: var(--white);
    border: 1px solid #e9ecef;
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
    font-size: var(--font-size-md);
    color: var(--gray-600);
    font-weight: var(--font-weight-medium);
  }

  .package-value {
    font-size: var(--font-size-md);
    color: var(--gray-900);
    font-weight: var(--font-weight-semibold);
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
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--gray-900);
  }

  .total-value {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--primary-blue);
  }

  .action-buttons {
    text-align: center;
  }

  .action-buttons .button {
    padding: 16px 32px;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    font-size: var(--font-size-3xl);
    margin: 0 auto 20px;
  }

  .modal-content h3 {
    font-size: var(--font-size-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin: 0 0 16px 0;
  }

  .modal-content p {
    color: var(--gray-600);
    margin: 0 0 8px 0;
  }

  .modal-actions .button {
    padding: 10px 20px;
    font-size: var(--font-size-md);
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
    font-size: var(--font-size-lg);
    margin-bottom: 10px;
  }

  .error-content p {
    font-size: var(--font-size-md);
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
    .booking-details-section {
      padding: 20px;
    }

    .combined-details {
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .room-image-container {
      height: 150px;
    }

    .detail-value {
      font-size: var(--font-size-md);
    }

    .section-title {
      font-size: var(--font-size-md);
    }

    .date-info {
      font-size: 16px;
    }

    .confirmation-number {
      font-size: var(--font-size-md);
    }
  }

  @media (max-width: 480px) {
    .confirmation-content {
      padding: 24px 0;
    }

    .success-header h1 {
      font-size: 24px;
    }

    .booking-details-section {
      padding: 20px;
    }
  }
</style>
