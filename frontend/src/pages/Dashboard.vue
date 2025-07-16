<template>
  <div class="dashboard">
    <div class="container">
      <!-- Welcome Header -->
      <div class="welcome-header">
        <div class="welcome-content">
          <h1>YOUR DASHBOARD</h1>
          <p>Manage your hotel bookings and reservations</p>
        </div>
      <div class="header-actions">
        <button @click="$router.push('/search')" class="button button--primary">
            + NEW BOOKING
        </button>
        </div>
      </div>

      <!-- Booking Tabs -->
      <div class="booking-tabs">
        <div class="tab-container">
            <button
            :class="['tab-button', { active: activeTab === 'upcoming' }]"
            @click="activeTab = 'upcoming'"
            >
            UPCOMING BOOKINGS ({{ upcomingBookings.length }})
            </button>
            <button
            :class="['tab-button', { active: activeTab === 'past' }]"
            @click="activeTab = 'past'"
          >
            PAST BOOKINGS ({{ pastBookings.length }})
          </button>
        </div>

        <!-- Upcoming Bookings -->
        <div v-if="activeTab === 'upcoming'" class="tab-content">
          <div v-if="upcomingBookings.length > 0" class="bookings-list">
            <div v-for="booking in upcomingBookings" :key="booking.id" class="booking-card">
              <div class="booking-header booking-header-grid">
                <div class="booking-header-info">
                  <h3 class="room-name">{{ booking.room?.name || 'DELUXE KING ROOM' }}</h3>
                  <div class="booking-reference">#{{ booking.booking_number || 'RES4CHADLDI' }}</div>
                </div>
                <div class="booking-status" :class="activeTab === 'upcoming' ? 'upcoming' : 'completed'">
                  {{ activeTab === 'upcoming' ? 'UPCOMING' : 'COMPLETED' }}
                </div>
              </div>

              <div class="booking-details">
                <div class="detail-grid">
                  <div class="detail-item">
                    <span class="label">CHECK-IN:</span>
                    <span class="value">{{ formatDate(booking.check_in) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">DURATION:</span>
                    <span class="value">{{ booking.nights || calculateNights(booking.check_in, booking.check_out) }} nights</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">TOTAL AMOUNT:</span>
                    <span class="value">S${{ booking.total }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">CHECK-OUT:</span>
                    <span class="value">{{ formatDate(booking.check_out) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">GUESTS:</span>
                    <span class="value">{{ booking.guests || 1 }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">BOOKED ON:</span>
                    <span class="value">{{ formatDate(booking.created_at) }}</span>
                  </div>
                </div>

                <div class="booking-actions">
                  <div class="room-image">
                    <img :src="booking.room?.image || '/api/placeholder/150/100'" :alt="booking.room?.name" />
                  </div>
                  <button @click="cancelBooking(booking.id)" class="button button--secondary cancel-btn">
                    CANCEL BOOKING
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>No upcoming bookings</p>
                </div>
              </div>

        <!-- Past Bookings -->
        <div v-if="activeTab === 'past'" class="tab-content">
          <div v-if="pastBookings.length > 0" class="bookings-list">
            <div v-for="booking in pastBookings" :key="booking.id" class="booking-card past">
              <div class="booking-header booking-header-grid">
                <div class="booking-header-info">
                  <h3 class="room-name">{{ booking.room?.name || 'DELUXE KING ROOM' }}</h3>
                  <div class="booking-reference">#{{ booking.booking_number }}</div>
                </div>
                <div class="booking-status completed">COMPLETED</div>
              </div>

              <div class="booking-details">
                <div class="detail-grid">
                  <div class="detail-item">
                    <span class="label">CHECK-IN:</span>
                    <span class="value">{{ formatDate(booking.check_in) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">DURATION:</span>
                    <span class="value">{{ booking.nights || calculateNights(booking.check_in, booking.check_out) }} nights</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">TOTAL AMOUNT:</span>
                    <span class="value">S${{ booking.total }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">CHECK-OUT:</span>
                    <span class="value">{{ formatDate(booking.check_out) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">GUESTS:</span>
                    <span class="value">{{ booking.guests || 1 }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">BOOKED ON:</span>
                    <span class="value">{{ formatDate(booking.created_at) }}</span>
              </div>
            </div>

            <div class="booking-actions">
                  <div class="room-image">
                    <img :src="booking.room?.image || '/api/placeholder/150/100'" :alt="booking.room?.name" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>No past bookings</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div v-if="showCancelModal" class="modal-overlay" @click="showCancelModal = false">
      <div class="modal-content" @click.stop>
        <h3>Cancel Booking</h3>
        <p>Are you sure you want to cancel this booking?</p>
        <div class="modal-actions">
          <button @click="showCancelModal = false" class="button button--secondary">
            No, Keep Booking
          </button>
          <button @click="confirmCancel" class="button button--danger">
            Yes, Cancel Booking
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores'
import { bookingsAPI } from '../api/bookings'

export default {
  name: 'Dashboard',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    const loading = ref(false)
    const error = ref('')
    const bookings = ref([])
    const activeTab = ref('upcoming')
    const showCancelModal = ref(false)
    const bookingToCancel = ref(null)

    const user = computed(() => authStore.user)

    const upcomingBookings = computed(() => {
      const today = new Date()
      return bookings.value.filter(booking => {
        const checkoutDate = new Date(booking.check_out)
        return checkoutDate >= today && booking.status !== 'cancelled'
      })
    })

    const pastBookings = computed(() => {
      const today = new Date()
      return bookings.value.filter(booking => {
        const checkoutDate = new Date(booking.check_out)
        return checkoutDate < today || booking.status === 'cancelled'
      })
    })

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    }

    const calculateNights = (checkin, checkout) => {
      const checkinDate = new Date(checkin)
      const checkoutDate = new Date(checkout)
      return Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24))
    }

    const loadBookings = async () => {
      loading.value = true
      error.value = ''

      try {
        const response = await bookingsAPI.getBookings()
        bookings.value = response.data || []
      } catch (err) {
        error.value = 'Failed to load bookings'
        console.error('Load bookings error:', err)
      } finally {
        loading.value = false
      }
    }

    const cancelBooking = (bookingId) => {
      bookingToCancel.value = bookingId
      showCancelModal.value = true
    }

    const confirmCancel = async () => {
      if (!bookingToCancel.value) return

      try {
        await bookingsAPI.cancelBooking(bookingToCancel.value)
        await loadBookings() // Reload bookings
        showCancelModal.value = false
        bookingToCancel.value = null
      } catch (error) {
        console.error('Cancel booking error:', error)
        alert('Failed to cancel booking')
      }
    }

    const logout = () => {
      authStore.logout()
      router.push('/login')
    }

    onMounted(() => {
      loadBookings()
    })

    return {
      loading,
      error,
      bookings,
      activeTab,
      showCancelModal,
      user,
      upcomingBookings,
      pastBookings,
      formatDate,
      calculateNights,
      cancelBooking,
      confirmCancel,
      logout
    }
  }
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.welcome-header {
  background: white;
  padding: 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.welcome-content h1 {
  font-size: 28px;
  font-weight: 700;
  color: #333;
  margin: 0 0 4px 0;
}

.welcome-content p {
  color: #6c757d;
  margin: 0;
  font-size: 16px;
}

.header-actions .button {
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.booking-tabs {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.tab-container {
  display: flex;
  border-bottom: 1px solid #e9ecef;
}

.tab-button {
  flex: 1;
  padding: 16px;
  background: #f8f9fa;
  border: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
  transition: all 0.2s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tab-button.active {
  background: var(--hotel-blue);
  color: white;
  border-bottom: 2px solid #333;
}

.tab-content {
  padding: 24px;
}

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.booking-card {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  overflow: hidden;
  transition: box-shadow 0.2s ease;
}

.booking-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.booking-card.past {
  opacity: 0.8;
}

.booking-header {
  background: #f8f9fa;
  padding: 16px;
  border-bottom: 1px solid #e9ecef;
}
.booking-header-grid {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 16px;
}

.booking-header-info {
  display: flex;
  flex-direction: column;
}

.booking-reference {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
}

.booking-status {
  padding: 8px 20px;
  border-radius: 4px;
  font-size: 18px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  white-space: nowrap;
  justify-self: end;
  display: inline-block;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  transition: background 0.2s, color 0.2s;
}

.booking-status.upcoming {
  background: #2563eb;
  color: var(--white);
}

.booking-status.completed {
  background: #22c55e;
  color: var(--white);
}

.booking-status.cancelled {
  background: #ef4444;
  color: var(--white);
}

.booking-details {
  padding: 24px;
  display: flex;
  gap: 24px;
}

.detail-grid {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px 24px;
  padding: 12px 0;
  margin-bottom: 0;
}
.detail-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.detail-item .label {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--hotel-blue);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 1px;
}
.detail-item .value {
  font-size: 16px;
  color: #222;
  font-weight: 700;
  letter-spacing: 0.1px;
}

.booking-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.room-image {
  width: 150px;
  height: 100px;
  border-radius: 8px;
  overflow: hidden;
}

.room-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cancel-btn {
  padding: 8px 16px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6c757d;
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
  padding: 28px;
  max-width: 400px;
  width: 90%;
  text-align: center;
}

.modal-content h3 {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin: 0 0 16px 0;
}

.modal-content p {
  color: #6c757d;
  margin: 0 0 24px 0;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.modal-actions .button {
  padding: 10px 20px;
  font-size: 14px;
}

@media (max-width: 768px) {
  .welcome-header {
    flex-direction: column;
    gap: 20px;
    text-align: center;
    padding: 24px;
  }

  .tab-container {
    flex-direction: column;
  }

  .booking-header {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }

  .booking-header-grid {
    grid-template-columns: 1fr;
    gap: 8px;
    text-align: center;
  }
  .booking-status {
    justify-self: center;
    font-size: 16px;
    padding: 8px 16px;
  }

  .booking-details {
    flex-direction: column;
  }

  .detail-grid {
    grid-template-columns: 1fr;
    padding: 8px 0;
    gap: 12px;
  }
  .detail-item .value {
    font-size: 15px;
  }
  .detail-item .label {
    font-size: 12px;
  }

  .booking-actions {
    align-items: stretch;
  }

  .room-image {
    width: 100%;
    height: 150px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 16px;
  }

  .welcome-header {
    padding: 20px;
  }

  .welcome-content h1 {
    font-size: 24px;
  }

  .tab-content {
    padding: 16px;
  }

  .booking-details {
    padding: 16px;
  }
}
</style>
