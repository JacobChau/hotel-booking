<template>
  <div class="room-search">
    <div class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">Find Your Perfect Stay</h1>
        <p class="hero-subtitle">Search from thousands of hotels worldwide</p>
      </div>
    </div>

    <div class="container">
      <div class="search-form-card">
        <form @submit.prevent="handleFormSubmit" class="search-form">
          <div class="search-row">
            <!-- Hotel Name Search -->
            <div class="search-field search-field--destination">
              <label class="search-label">
                <span class="search-icon"><i class="pi pi-map-marker"></i></span>
                Where are you going?
              </label>
              <input
                type="text"
                v-model="searchParams.destination"
                placeholder="Enter hotel name, city, or destination"
                class="search-input"
              />
            </div>

            <!-- Date Range Picker Component -->
            <DateRangePicker
              v-model:checkin="searchParams.checkin"
              v-model:checkout="searchParams.checkout"
            />

            <!-- Guest Selector Component -->
            <GuestSelector
              v-model:guests="searchParams.guests"
            />

            <!-- Search Button -->
            <div class="search-field search-field--button">
              <button type="submit" class="search-button" :disabled="loading || !searchParams.checkin || !searchParams.checkout">
                <i class="pi pi-search"></i> Search
              </button>
            </div>
          </div>
        </form>
      </div>

      <div v-if="loading && (!searchResults.data || searchResults.data.length === 0)" class="loading">
        <div class="loading-content">
          <p class="loading-text">Searching for available rooms...</p>
        </div>
      </div>

      <div v-if="error" class="alert alert--error">
        {{ error }}
      </div>

      <div v-if="searchResults.data && searchResults.data.length > 0" class="search-results">
        <div class="results-header">
          <h3>{{ searchResults.total }} Available Rooms</h3>
          <p class="results-subtitle">
            {{ searchParams.destination || 'Available rooms' }} • 
            {{ formatDateRange() }} • 
            {{ formatGuestsSummary() }}
          </p>
        </div>
        
        <div class="results-container" :class="{ 'results-container--loading': paginationLoading || (loading && searchResults.data && searchResults.data.length > 0) }">
          <!-- Loading Overlay for pagination or new search with existing results -->
          <div v-if="paginationLoading || (loading && searchResults.data && searchResults.data.length > 0)" class="pagination-loading-overlay">
            <div class="pagination-loading-content">
              <div class="loading-spinner"></div>
              <p v-if="paginationLoading">Loading more rooms...</p>
              <p v-else-if="loading">Searching for available rooms...</p>
            </div>
          </div>
          
          <div class="rooms-list">
            <div v-for="room in searchResults.data" :key="room.id" class="room-card">
              <div class="room-image-container">
                <img :src="room.image" :alt="room.name" class="room-image" />
                <div class="room-badge">Available</div>
              </div>
              <div class="room-info">
                <h4 class="room-name">{{ room.name }}</h4>
                <p class="room-description">{{ room.description }}</p>
                <div class="room-features">
                  <span class="feature"><i class="pi pi-users"></i> {{ room.capacity }} guests</span>
                  <span class="feature"><i class="pi pi-th-large"></i> {{ room.size }} sqft</span>
                  <span class="feature"><i class="pi pi-home"></i> {{ room.beds || 'Queen bed' }}</span>
                </div>
                <div class="room-footer">
                  <div class="room-price">
                    <span class="price">S${{ room.price }}</span>
                    <span class="price-unit">/ night</span>
                  </div>
                  <button @click="selectRoom(room)" class="button button--primary">
                    Select Room
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="pagination-wrapper">
            <Pagination 
              :pagination-data="searchResults" 
              :loading="paginationLoading"
              @page-change="goToPage"
            />
          </div>
        </div>
      </div>

      <div v-if="!loading && searchResults.data && searchResults.data.length === 0 && searchParams.checkin" class="no-results">
        <div class="no-results-content">
          <div class="no-results-icon"><i class="pi pi-home"></i></div>
          <h3>No rooms available</h3>
          <p>Try adjusting your search criteria or dates</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {onMounted, reactive, ref} from 'vue'
import {useRouter} from 'vue-router'
import {bookingsAPI} from '../api/bookings'
import {useBookingStore} from '../stores'
import DateRangePicker from '../components/DateRangePicker.vue'
import GuestSelector from '../components/GuestSelector.vue'
import Pagination from '../components/Pagination.vue'
import { useAuthStore } from '../stores'

export default {
  name: 'RoomSearch',
  components: {
    DateRangePicker,
    GuestSelector,
    Pagination
  },
  setup() {
    const router = useRouter()
    const bookingStore = useBookingStore()
    const authStore = useAuthStore()

    const loading = ref(false)
    const paginationLoading = ref(false)
    const error = ref('')
    const searchResults = ref({
      data: [],
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0
    })

    const searchParams = reactive({
      destination: '',
      checkin: '',
      checkout: '',
      guests: 2,
      page: 1
    })

    onMounted(() => {
      if (bookingStore.searchParams.checkin && bookingStore.searchParams.checkout) {
        searchParams.checkin = bookingStore.searchParams.checkin
        searchParams.checkout = bookingStore.searchParams.checkout
        searchParams.guests = bookingStore.searchParams.guests
        searchParams.destination = bookingStore.searchParams.destination
        
        searchRooms()
      } else {
        const today = new Date()
        const tomorrow = new Date(today)
        tomorrow.setDate(tomorrow.getDate() + 1)
        
        searchParams.checkin = today.toISOString().split('T')[0]
        searchParams.checkout = tomorrow.toISOString().split('T')[0]
        
        bookingStore.setSearchParams({
          checkin: searchParams.checkin,
          checkout: searchParams.checkout,
          guests: searchParams.guests,
          destination: searchParams.destination
        })
      }
    })

    const formatDateRange = () => {
      if (!searchParams.checkin || !searchParams.checkout) return ''
      
      const checkinDate = new Date(searchParams.checkin)
      const checkoutDate = new Date(searchParams.checkout)
      
      const options = { month: 'short', day: 'numeric' }
      const checkinStr = checkinDate.toLocaleDateString('en-US', options)
      const checkoutStr = checkoutDate.toLocaleDateString('en-US', options)
      
      const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24))
      
      return `${checkinStr} — ${checkoutStr} (${nights} night${nights > 1 ? 's' : ''})`
    }

    const formatGuestsSummary = () => {
      return `${searchParams.guests} guest${searchParams.guests > 1 ? 's' : ''}`
    }

    const handleFormSubmit = () => {
      searchRooms(1, false)
    }

    const searchRooms = async (page = 1, isPagination = false) => {
      if (paginationLoading.value && !isPagination) {
        return
      }
      
      if (!isPagination) {
        loading.value = true
      }
      error.value = ''
      searchParams.page = page

      bookingStore.setSearchParams({
        checkin: searchParams.checkin,
        checkout: searchParams.checkout,
        guests: searchParams.guests,
        destination: searchParams.destination
      })

      try {
        const apiParams = {
          checkin: searchParams.checkin,
          checkout: searchParams.checkout,
          guests: searchParams.guests,
          destination: searchParams.destination,
          page: page
        }
        
        const response = await bookingsAPI.searchRooms(apiParams)
        searchResults.value = response
      } catch (err) {
        error.value = 'Failed to search rooms. Please try again.'
        console.error('Room search error:', err)
      } finally {
        if (!isPagination) {
          loading.value = false
        }
      }
    }

    const goToPage = (page) => {
      if (page >= 1 && page <= searchResults.value.last_page && !paginationLoading.value) {
        paginationLoading.value = true
        searchRooms(page, true).finally(() => {
          paginationLoading.value = false
        })
      }
    }

    const selectRoom = (room) => {
      if (!authStore.isAuthenticated) {
        bookingStore.setSelectedRoom(room)
        bookingStore.setBookingDates(searchParams.checkin, searchParams.checkout)
        bookingStore.setGuestCount(searchParams.guests)
        
        bookingStore.setSearchParams({
          checkin: searchParams.checkin,
          checkout: searchParams.checkout,
          guests: searchParams.guests,
          destination: searchParams.destination
        })
        
        sessionStorage.setItem('redirectAfterLogin', '/contact-info')
        
        router.push('/login')
        return
      }
      
      bookingStore.setSelectedRoom(room)
      bookingStore.setBookingDates(searchParams.checkin, searchParams.checkout)
      bookingStore.setGuestCount(searchParams.guests)
      
      bookingStore.setSearchParams({
        checkin: searchParams.checkin,
        checkout: searchParams.checkout,
        guests: searchParams.guests,
        destination: searchParams.destination
      })
      
      router.push('/contact-info')
    }

    const clearSearch = () => {
      searchParams.destination = ''
      searchResults.value = {
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      }
      error.value = ''
    }

    return {
      searchParams,
      loading,
      paginationLoading,
      error,
      searchResults,
      handleFormSubmit,
      searchRooms,
      selectRoom,
      clearSearch,
      formatDateRange,
      formatGuestsSummary,
      goToPage
    }
  }
}
</script>

<style scoped>
.room-search {
  min-height: 100vh;
  background: #f5f5f5;
}

.hero-section {
  background: linear-gradient(135deg, #003580 0%, #0071c2 100%);
  padding: 80px 0 120px;
  text-align: center;
  color: white;
}

.hero-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

.hero-title {
  font-size: 48px;
  font-weight: 700;
  margin: 0 0 16px 0;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 20px;
  margin: 0;
  opacity: 0.9;
}

.search-form-card {
  background: #febb02;
  border-radius: 8px;
  padding: 4px;
  margin: -60px auto 40px;
  position: relative;
  z-index: 10;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.search-form {
  background: white;
  border-radius: 4px;
  padding: 0;
}

.search-row {
  display: flex;
  align-items: stretch;
  min-height: 60px;
}

.search-field {
  flex: 1;
  border-right: 1px solid #e0e0e0;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  cursor: pointer;
}

.search-field:last-child {
  border-right: none;
}

.search-field--destination {
  flex: 2;
}

.search-field--button {
  flex: 0 0 120px;
  padding: 4px;
  cursor: default;
}

.search-label {
  font-size: 12px;
  font-weight: 600;
  color: #003580;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.search-icon {
  font-size: 14px;
}

.search-input {
  border: none;
  outline: none;
  font-size: 14px;
  color: #333;
  background: transparent;
  width: 100%;
}

.search-input::placeholder {
  color: #999;
}

.search-button {
  width: 100%;
  height: 100%;
  background: var(--primary-blue);
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.search-button:hover:not(:disabled) {
  background: var(--primary-blue-dark);
}

.search-button:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.7;
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-text {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.search-results {
  margin-top: 48px;
}

.results-header {
  text-align: center;
  margin-bottom: 32px;
}

.results-header h3 {
  font-size: 32px;
  font-weight: 600;
  color: #333;
  margin: 0 0 8px 0;
}

.results-subtitle {
  font-size: 16px;
  color: #666;
  margin: 0;
}

.results-container {
  position: relative;
}

.results-container--loading {
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

.rooms-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}


.room-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
  display: flex;
  min-height: 200px;
}

.room-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.room-image-container {
  position: relative;
  width: 280px;
  height: 200px;
  overflow: hidden;
  flex-shrink: 0;
}

.room-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.room-card:hover .room-image {
  transform: scale(1.05);
}

.room-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: #00b900;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.room-info {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.room-name {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0 0 8px 0;
}

.room-description {
  color: #666;
  font-size: 14px;
  line-height: 1.4;
  margin: 0 0 16px 0;
}

.room-features {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
}

.feature {
  font-size: 13px;
  color: #666;
  display: flex;
  align-items: center;
  gap: 4px;
}

.room-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.room-price {
  display: flex;
  align-items: baseline;
  gap: 4px;
}

.price {
  font-size: 20px;
  font-weight: 700;
  color: #0071c2;
}

.price-unit {
  font-size: 14px;
  color: #666;
}

.no-results {
  text-align: center;
  padding: 60px 20px;
}

.no-results-content {
  max-width: 400px;
  margin: 0 auto;
}

.no-results-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.no-results h3 {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin: 0 0 12px 0;
}

  .no-results p {
    color: #666;
    margin: 0 0 20px 0;
  }

  /* Responsive Design */
@media (max-width: 768px) {
  .hero-title {
    font-size: 36px;
  }

  .hero-subtitle {
    font-size: 18px;
  }

  .search-form-card {
    margin: -40px auto 30px;
  }

  .search-row {
    flex-direction: column;
    min-height: auto;
  }

  .search-field {
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
    padding: 16px;
  }

  .search-field:last-child {
    border-bottom: none;
  }

  .search-field--button {
    flex: none;
    padding: 16px;
  }

  .search-button {
    height: 48px;
  }

  .room-card {
    flex-direction: column;
    min-height: auto;
  }

  .room-image-container {
    width: 100%;
    height: 200px;
  }

  .room-footer {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 28px;
  }

  .search-form-card {
    margin: -30px auto 20px;
  }

  .room-info {
    padding: 16px;
  }
}
</style>
