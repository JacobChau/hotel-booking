<template>
  <div class="rooms-page">
    <div class="container">
      <div class="page-header">
        <h2>Available Rooms</h2>
        <div class="search-summary">
          <span>{{ formatDate(searchParams.checkin) }} → {{ formatDate(searchParams.checkout) }}</span>
          <span>{{ searchParams.guests }} guest{{ searchParams.guests > 1 ? 's' : '' }}</span>
          <span>{{ numberOfNights }} night{{ numberOfNights > 1 ? 's' : '' }}</span>
        </div>
      </div>

      <div v-if="loading" class="loading">
        Loading rooms...
      </div>

      <div v-if="error" class="alert alert--error">
        {{ error }}
      </div>

      <div v-if="rooms.length > 0" class="rooms-section">
        <div class="section-info">
          <p>Showing {{ rooms.length }} of {{ rooms.length }} rooms</p>
        </div>

        <div class="rooms-list">
          <div v-for="room in rooms" :key="room.id" class="room-item">
            <div class="room-image-container">
              <img :src="room.image" :alt="room.name" class="room-image" />
            </div>

            <div class="room-details">
              <h3 class="room-name">{{ room.name }}</h3>
              <p class="room-description">{{ room.description }}</p>

              <div class="room-amenities" v-if="room.amenities">
                <h4>Amenities:</h4>
                <ul>
                  <li v-for="amenity in room.amenities" :key="amenity">{{ amenity }}</li>
                </ul>
              </div>

              <div class="room-specs">
                <div class="spec-item">
                  <strong>Capacity:</strong> {{ room.capacity }} guests
                </div>
                <div class="spec-item">
                  <strong>Size:</strong> {{ room.size }} sqft
                </div>
                <div class="spec-item" v-if="room.bed_type">
                  <strong>Bed Type:</strong> {{ room.bed_type }}
                </div>
              </div>
            </div>

            <div class="room-pricing">
              <div class="price-display">
                <span class="currency">S$</span>
                <span class="price">{{ room.price }}</span>
                <span class="price-unit">/night</span>
              </div>

              <div class="total-price">
                <span class="total-label">Total: </span>
                <span class="total-amount">S${{ calculateTotal(room.price) }}</span>
              </div>
              
              <div class="price-note">Subject to GST and charges</div>

              <button
                @click="selectRoom(room)"
                class="button button--primary select-btn"
              >
                BOOK ROOM
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && rooms.length === 0" class="no-rooms">
        <div class="no-rooms-content">
          <div class="no-rooms-icon"><i class="pi pi-home"></i></div>
          <h3>No rooms available</h3>
          <p>No rooms available for your selected dates.</p>
          <button @click="$router.push('/search')" class="button button--secondary">
            Search Different Dates
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { bookingsAPI } from '../api/bookings'
import { useBookingStore, useAuthStore } from '../stores'

export default {
  name: 'Rooms',
  setup() {
    const router = useRouter()
    const bookingStore = useBookingStore()
    const authStore = useAuthStore()

    const loading = ref(false)
    const error = ref('')
    const rooms = ref([])

    const searchParams = computed(() => bookingStore.searchParams)

    const numberOfNights = computed(() => {
      const checkin = new Date(searchParams.value.checkin)
      const checkout = new Date(searchParams.value.checkout)
      return Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24))
    })

    const calculateTotal = (pricePerNight) => {
      return (pricePerNight * numberOfNights.value).toFixed(2)
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    }

    const loadRooms = async () => {
      loading.value = true
      error.value = ''

      try {
        const response = await bookingsAPI.searchRooms(searchParams.value)
        rooms.value = response.data || []
      } catch (err) {
        error.value = 'Failed to load rooms. Please try again.'
        console.error('Load rooms error:', err)
      } finally {
        loading.value = false
      }
    }

    const selectRoom = (room) => {
      // Check if user is authenticated
      if (!authStore.isAuthenticated) {
        // Store the selected room and booking details for after login
        bookingStore.setSelectedRoom(room)
        
        // Store the intended destination (contact-info page) for redirect after login
        sessionStorage.setItem('redirectAfterLogin', '/contact-info')
        
        // Redirect to login
        router.push('/login')
        return
      }
      
      // User is authenticated, proceed normally
      bookingStore.setSelectedRoom(room)
      router.push('/contact-info')
    }

    onMounted(() => {
      if (!searchParams.value.checkin || !searchParams.value.checkout) {
        router.push('/search')
        return
      }
      loadRooms()
    })

    return {
      loading,
      error,
      rooms,
      searchParams,
      numberOfNights,
      calculateTotal,
      formatDate,
      selectRoom
    }
  }
}
</script>

<style scoped>
.rooms-page {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.page-header {
  background: white;
  padding: 32px 0;
  margin-bottom: 24px;
  border-bottom: 1px solid #e9ecef;
}

.page-header h2 {
  font-size: 32px;
  font-weight: 700;
  color: #333;
  margin: 0 0 16px 0;
  text-align: center;
}

.search-summary {
  display: flex;
  justify-content: center;
  gap: 24px;
  color: #6c757d;
  font-size: 16px;
}

.search-summary span {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rooms-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.section-info {
  padding: 20px 24px;
  border-bottom: 1px solid #e9ecef;
  background: #f8f9fa;
}

.section-info p {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
}

.rooms-list {
  display: flex;
  flex-direction: column;
}

.room-item {
  display: grid;
  grid-template-columns: 200px 1fr 280px;
  gap: 24px;
  padding: 24px;
  border-bottom: 1px solid #e9ecef;
  transition: background-color 0.2s ease;
}

.room-item:last-child {
  border-bottom: none;
}

.room-item:hover {
  background-color: #f8f9fa;
}

.room-image-container {
  position: relative;
}

.room-image {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 8px;
}

.room-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.room-name {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin: 0;
  text-transform: uppercase;
}

.room-description {
  color: #6c757d;
  font-size: 14px;
  line-height: 1.5;
  margin: 0;
}

.room-amenities {
  margin-top: 8px;
}

.room-amenities h4 {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  margin: 0 0 8px 0;
}

.room-amenities ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.room-amenities li {
  background: #e9ecef;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #495057;
}

.room-specs {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.spec-item {
  color: #6c757d;
  font-size: 14px;
}

.room-pricing {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: space-between;
  text-align: right;
}

.price-display {
  display: flex;
  align-items: baseline;
  gap: 2px;
  margin-bottom: 8px;
}

.currency {
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.price {
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.price-unit {
  font-size: 14px;
  color: #6c757d;
}

.total-price {
  margin-bottom: 4px;
}

.total-label {
  font-size: 14px;
  color: #6c757d;
}

.total-amount {
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.price-note {
  font-size: 12px;
  color: #6c757d;
  margin-bottom: 16px;
}

.select-btn {
  width: 120px;
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.no-rooms {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.no-rooms-content {
  max-width: 400px;
  margin: 0 auto;
}

.no-rooms-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.no-rooms h3 {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin: 0 0 12px 0;
}

.no-rooms p {
  color: #6c757d;
  margin: 0 0 24px 0;
}

@media (max-width: 768px) {
  .search-summary {
    flex-direction: column;
    gap: 8px;
  }

  .room-item {
    grid-template-columns: 1fr;
    gap: 16px;
    text-align: center;
  }

  .room-image {
    height: 200px;
  }

  .room-pricing {
    align-items: center;
    text-align: center;
  }

  .select-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 16px;
  }

  .page-header {
    padding: 24px 0;
  }

  .page-header h2 {
    font-size: 24px;
  }

  .room-item {
    padding: 20px;
  }

  .price {
    font-size: 28px;
  }
}
</style>
