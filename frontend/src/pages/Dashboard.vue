<template>
  <div class="dashboard-wrapper">
    <div class="dashboard">
      <div class="container">
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

      <div class="booking-tabs">
        <div class="tab-container">
            <button
            :class="['tab-button', { active: activeTab === 'upcoming' }]"
            @click="switchTab('upcoming')"
            :disabled="tabLoading"
            >
            UPCOMING BOOKINGS ({{ activeTab === 'upcoming' ? paginationData.total : (tabDataCache.upcoming.loaded ? tabDataCache.upcoming.total : '...') }})
            </button>
            <button
            :class="['tab-button', { active: activeTab === 'past' }]"
            @click="switchTab('past')"
            :disabled="tabLoading"
          >
            PAST BOOKINGS ({{ activeTab === 'past' ? paginationData.total : (tabDataCache.past.loaded ? tabDataCache.past.total : '...') }})
          </button>
        </div>

        <div v-if="activeTab === 'upcoming'" class="tab-content">
          <div v-if="tabLoading" class="tab-loading-overlay">
            <div class="tab-loading-content">
              <div class="loading-spinner"></div>
              <p>Loading upcoming bookings...</p>
            </div>
          </div>
          
          <div v-else-if="currentBookings.length > 0" class="bookings-container" :class="{ 'bookings-container--loading': paginationLoading }">
            <div v-if="paginationLoading" class="pagination-loading-overlay">
              <div class="pagination-loading-content">
                <div class="loading-spinner"></div>
                <p>Loading bookings...</p>
              </div>
            </div>
            
            <div class="bookings-list">
            <div v-for="booking in currentBookings" :key="booking.id" class="booking-card">
              <div class="booking-header booking-header-grid">
                <div class="booking-header-info">
                  <h3 class="room-name">{{ booking.room?.name || 'DELUXE KING ROOM' }}</h3>
                  <div class="booking-reference">#{{ booking.booking_number || 'RES4CHADLDI' }}</div>
                </div>
                <div class="booking-status-container">
                  <div class="booking-status" :class="getStatusClass(booking)">
                    <div class="status-indicator"></div>
                    <span class="status-text">{{ getStatusText(booking) }}</span>
                  </div>
                  <div class="booking-date-badge">
                    <i class="pi pi-calendar"></i>
                    <span>{{ formatDateShort(booking.check_in) }}</span>
                  </div>
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
                  <button 
                    @click="cancelBooking(booking.id)" 
                    class="button button--danger cancel-btn"
                    :disabled="!canCancelBooking(booking)"
                    v-if="activeTab === 'upcoming'"
                  >
                    CANCEL BOOKING
                  </button>
                </div>
              </div>
            </div>
            </div>
            
            <Pagination
              :pagination-data="paginationData"
              :loading="paginationLoading"
              @page-change="handlePageChange"
            />
          </div>
          <div v-else class="empty-state">
            <p>No upcoming bookings</p>
          </div>
        </div>

        <div v-if="activeTab === 'past'" class="tab-content">
          <div v-if="tabLoading" class="tab-loading-overlay">
            <div class="tab-loading-content">
              <div class="loading-spinner"></div>
              <p>Loading past bookings...</p>
            </div>
          </div>
          
          <div v-else-if="currentBookings.length > 0" class="bookings-container" :class="{ 'bookings-container--loading': paginationLoading }">
            <div v-if="paginationLoading" class="pagination-loading-overlay">
              <div class="pagination-loading-content">
                <div class="loading-spinner"></div>
                <p>Loading bookings...</p>
              </div>
            </div>
            
            <div class="bookings-list">
            <div v-for="booking in currentBookings" :key="booking.id" class="booking-card past">
              <div class="booking-header booking-header-grid">
                <div class="booking-header-info">
                  <h3 class="room-name">{{ booking.room?.name || 'DELUXE KING ROOM' }}</h3>
                  <div class="booking-reference">#{{ booking.booking_number }}</div>
                </div>
                <div class="booking-status-container">
                  <div class="booking-status" :class="getStatusClass(booking)">
                    <div class="status-indicator"></div>
                    <span class="status-text">{{ getStatusText(booking) }}</span>
                  </div>
                  <div class="booking-date-badge">
                    <i class="pi pi-calendar"></i>
                    <span>{{ formatDateShort(booking.check_in) }}</span>
                  </div>
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
                </div>
              </div>
            </div>
            </div>
            
            <Pagination
              :pagination-data="paginationData"
              :loading="paginationLoading"
              @page-change="handlePageChange"
            />
          </div>
          <div v-else class="empty-state">
            <p>No past bookings</p>
          </div>
        </div>
      </div>
    </div>

      <NotificationToast :notification="notification" @hide="hideNotification" />
    </div>

    <!-- Cancel Booking Modal - Inside wrapper but with absolute positioning -->
    <Teleport to="body">
      <div v-if="showCancelModal" class="modal-overlay" @click="showCancelModal = false">
        <div class="modal-content" @click.stop>
          <h3>Cancel Booking</h3>
          <p>Are you sure you want to cancel this booking?</p>
          <div class="modal-actions">
            <button @click="showCancelModal = false" class="button button--secondary">
              No, Keep Booking
            </button>
            <button @click="confirmCancel" class="button button--danger" :disabled="loading">
              {{ loading ? 'Cancelling...' : 'Yes, Cancel Booking' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores'
import { bookingsAPI } from '../api/bookings'
import Pagination from '../components/Pagination.vue'
import NotificationToast from '../components/NotificationToast.vue'

export default {
  name: 'Dashboard',
  components: {
    Pagination,
    NotificationToast
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    const loading = ref(false)
    const paginationLoading = ref(false)
    const tabLoading = ref(false)
    const error = ref('')
    const activeTab = ref('upcoming')
    const showCancelModal = ref(false)
    const bookingToCancel = ref(null)
    const currentPage = ref(1)
    const itemsPerPage = 5
    const notification = ref({
      show: false,
      message: '',
      type: 'success'
    })
    
    const tabDataCache = ref({
      upcoming: {
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 5,
        total: 0,
        from: 0,
        to: 0,
        loaded: false
      },
      past: {
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 5,
        total: 0,
        from: 0,
        to: 0,
        loaded: false
      }
    })
    
    const paginationData = computed(() => {
      return tabDataCache.value[activeTab.value]
    })

    const user = computed(() => authStore.user)

    const currentBookings = computed(() => {
      return paginationData.value.data || []
    })

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    }

    const formatDateShort = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      })
    }

    const getStatusClass = (booking) => {
      const today = new Date()
      const checkoutDate = new Date(booking.check_out)
      const checkinDate = new Date(booking.check_in)
      
      if (booking.status === 'cancelled') {
        return 'cancelled'
      } else if (checkoutDate < today) {
        return 'completed'
      } else if (checkinDate <= today && checkoutDate >= today) {
        return 'active'
      } else {
        return 'upcoming'
      }
    }

    const getStatusText = (booking) => {
      const today = new Date()
      const checkoutDate = new Date(booking.check_out)
      const checkinDate = new Date(booking.check_in)
      
      if (booking.status === 'cancelled') {
        return 'CANCELLED'
      } else if (checkoutDate < today) {
        return 'COMPLETED'
      } else if (checkinDate <= today && checkoutDate >= today) {
        return 'ACTIVE'
      } else {
        return 'UPCOMING'
      }
    }

    const canCancelBooking = (booking) => {
      if (booking.status === 'cancelled') {
        return false
      }
      
      const checkinDate = new Date(booking.check_in)
      
      const oneDayFromNow = new Date()
      oneDayFromNow.setDate(oneDayFromNow.getDate() + 1)
      
      return checkinDate >= oneDayFromNow
    }

    const showNotification = (message, type = 'success') => {
      notification.value = {
        show: true,
        message,
        type
      }
      
      setTimeout(() => {
        notification.value.show = false
      }, 3000)
    }

    const hideNotification = () => {
      notification.value.show = false
    }

    const handlePageChange = (page) => {
      if (page >= 1 && page <= paginationData.value.last_page && !paginationLoading.value) {
        loadBookings(page, true)
      }
    }

    const switchTab = async (tab) => {
      if (activeTab.value === tab) return
      
      activeTab.value = tab
      currentPage.value = 1
      
      if (tabDataCache.value[tab].loaded) {
        return
      }
      
      tabLoading.value = true
      await loadBookings(1, false)
      tabLoading.value = false
    }

    const calculateNights = (checkin, checkout) => {
      const checkinDate = new Date(checkin)
      const checkoutDate = new Date(checkout)
      return Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24))
    }

    const loadBookings = async (page = 1, isPagination = false) => {
      if (isPagination) {
        paginationLoading.value = true
      } else if (!tabLoading.value) {
        loading.value = true
      }
      error.value = ''

      try {
        const params = {
          page,
          per_page: itemsPerPage,
          type: activeTab.value
        }
        
        const response = await bookingsAPI.getBookings(params)
        
        if (response.success) {
          tabDataCache.value[activeTab.value] = {
            data: response.data,
            current_page: response.current_page,
            last_page: response.last_page,
            per_page: response.per_page,
            total: response.total,
            from: response.from,
            to: response.to,
            loaded: true
          }
          currentPage.value = response.current_page
        } else {
          throw new Error('Failed to load bookings')
        }
      } catch (err) {
        error.value = 'Failed to load bookings'
        console.error('Load bookings error:', err)
      } finally {
        if (isPagination) {
          paginationLoading.value = false
        } else if (!tabLoading.value) {
          loading.value = false
        }
      }
    }

    const cancelBooking = (bookingId) => {
      bookingToCancel.value = bookingId
      showCancelModal.value = true
    }

    const confirmCancel = async () => {
      if (!bookingToCancel.value) return

      loading.value = true

      try {
        const response = await bookingsAPI.cancelBooking(bookingToCancel.value)
        
        if (response.success) {
          tabDataCache.value.upcoming.loaded = false
          tabDataCache.value.past.loaded = false
          
          await loadBookings()
          showCancelModal.value = false
          bookingToCancel.value = null
          
          showNotification('Booking cancelled successfully', 'success')
        } else {
          throw new Error(response.message || 'Failed to cancel booking')
        }
      } catch (error) {
        console.error('Cancel booking error:', error)
        const errorMessage = error.response?.data?.message || error.message || 'Failed to cancel booking. Please try again.'
        showNotification(errorMessage, 'error')
      } finally {
        loading.value = false
      }
    }

    const logout = () => {
      authStore.logout()
      router.push('/login')
    }

    onMounted(() => {
      loadBookings(1, false)
    })

    return {
      loading,
      paginationLoading,
      tabLoading,
      error,
      activeTab,
      showCancelModal,
      user,
      currentBookings,
      paginationData,
      tabDataCache,
      notification,
      formatDate,
      formatDateShort,
      getStatusClass,
      getStatusText,
      canCancelBooking,
      calculateNights,
      cancelBooking,
      confirmCancel,
      handlePageChange,
      switchTab,
      showNotification,
      hideNotification,
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

.tab-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.tab-content {
  padding: 32px
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

.booking-status-container {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
}

.booking-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-text {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-bold);
  letter-spacing: 0.8px;
}

.booking-status.upcoming {
  background: rgba(37, 99, 235, 0.1);
  color: #2563eb;
  border-color: rgba(37, 99, 235, 0.2);
}

.booking-status.upcoming .status-indicator {
  background: #2563eb;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.booking-status.active {
  background: rgba(251, 146, 60, 0.1);
  color: #ea580c;
  border-color: rgba(251, 146, 60, 0.2);
}

.booking-status.active .status-indicator {
  background: #ea580c;
  box-shadow: 0 0 0 2px rgba(251, 146, 60, 0.2);
  animation: pulse 2s infinite;
}

.booking-status.completed {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
  border-color: rgba(34, 197, 94, 0.2);
}

.booking-status.completed .status-indicator {
  background: #16a34a;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}

.booking-status.cancelled {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.2);
}

.booking-status.cancelled .status-indicator {
  background: #dc2626;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.booking-date-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: rgba(107, 114, 128, 0.1);
  color: #6b7280;
  border-radius: 12px;
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.booking-date-badge i {
  font-size: 10px;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
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
  padding: 4px 10px;
  font-size: 13px;
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.2s ease;
}

.cancel-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background-color: #6c757d !important;
  border-color: #6c757d !important;
}

.cancel-btn:disabled:hover {
  background-color: #6c757d !important;
  border-color: #6c757d !important;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6c757d;
}

.modal-overlay {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  background: rgba(0, 0, 0, 0.5);
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  z-index: 9999 !important;
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 8px;
  padding: 28px;
  max-width: 400px;
  width: 90%;
  max-height: 90vh;
  text-align: center;
  margin: auto;
  position: relative;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  overflow-y: auto;
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

.button--danger {
  background-color: var(--danger-red);
  color: var(--white);
  border: 1px solid var(--danger-red);
}

.button--danger:hover:not(:disabled) {
  background-color: #c82333;
  border-color: #bd2130;
}

.button--danger:focus {
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
}

.bookings-container {
  position: relative;
}

.bookings-container--loading {
  opacity: 0.7;
  pointer-events: none;
}

.pagination-loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border-radius: 8px;
}

.pagination-loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 20px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #0071c2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.pagination-loading-content p {
  margin: 0;
  color: #666;
  font-weight: 500;
}

.tab-loading-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 8px;
}

.tab-loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 40px;
}

.tab-loading-content .loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #0071c2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.tab-loading-content p {
  margin: 0;
  color: #666;
  font-weight: 500;
  font-size: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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
    gap: 12px;
    text-align: center;
  }
  
  .booking-status-container {
    align-items: center;
    gap: 12px;
  }
  
  .booking-status {
    font-size: var(--font-size-md);
    padding: 6px 10px;
  }
  
  .booking-date-badge {
    font-size: 10px;
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
