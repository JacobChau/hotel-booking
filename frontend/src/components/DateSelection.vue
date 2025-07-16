<template>
  <div class="date-selection">
    <h2>Select Your Dates</h2>

    <div class="selected-room-info">
      <h3>{{ selectedRoom.name }}</h3>
      <p class="room-price">${{ selectedRoom.price }} per night</p>
    </div>

    <div class="date-inputs">
      <div class="date-group">
        <label for="checkin">Check-in Date:</label>
        <input
          id="checkin"
          v-model="checkInDate"
          type="date"
          :min="minDate"
          class="date-input"
          required
        />
      </div>

      <div class="date-group">
        <label for="checkout">Check-out Date:</label>
        <input
          id="checkout"
          v-model="checkOutDate"
          type="date"
          :min="checkInDate || minDate"
          class="date-input"
          required
        />
      </div>
    </div>

    <div v-if="dateError" class="error-message">
      {{ dateError }}
    </div>

    <div v-if="totalNights > 0" class="booking-summary">
      <div class="summary-item">
        <span>Number of nights:</span>
        <span>{{ totalNights }}</span>
      </div>
      <div class="summary-item">
        <span>Price per night:</span>
        <span>${{ selectedRoom.price }}</span>
      </div>
      <div class="summary-item total">
        <span>Total price:</span>
        <span>${{ totalPrice }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, watch } from 'vue'
import { useBookingStore } from '../stores/booking'

export default {
  name: 'DateSelection',
  setup() {
    const bookingStore = useBookingStore()

    const selectedRoom = computed(() => bookingStore.selectedRoom)
    const totalNights = computed(() => bookingStore.totalNights)
    const totalPrice = computed(() => bookingStore.totalPrice)

    const checkInDate = computed({
      get: () => bookingStore.checkInDate,
      set: (value) => {
        bookingStore.setDates(value, bookingStore.checkOutDate)
      }
    })

    const checkOutDate = computed({
      get: () => bookingStore.checkOutDate,
      set: (value) => {
        bookingStore.setDates(bookingStore.checkInDate, value)
      }
    })

    const minDate = computed(() => {
      const today = new Date()
      return today.toISOString().split('T')[0]
    })

    const dateError = computed(() => {
      if (!checkInDate.value || !checkOutDate.value) return ''

      const checkIn = new Date(checkInDate.value)
      const checkOut = new Date(checkOutDate.value)
      const today = new Date()

      if (checkIn < today) {
        return 'Check-in date cannot be in the past'
      }

      if (checkOut <= checkIn) {
        return 'Check-out date must be after check-in date'
      }

      return ''
    })

    watch([checkInDate, checkOutDate], () => {
      if (checkInDate.value && checkOutDate.value && !dateError.value) {
        bookingStore.setDates(checkInDate.value, checkOutDate.value)
      }
    })

    return {
      selectedRoom,
      checkInDate,
      checkOutDate,
      minDate,
      dateError,
      totalNights,
      totalPrice
    }
  }
}
</script>

<style scoped>
.date-selection h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.selected-room-info {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  text-align: center;
}

.selected-room-info h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.room-price {
  color: #667eea;
  font-weight: bold;
  margin: 0;
}

.date-inputs {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.date-group {
  display: flex;
  flex-direction: column;
}

.date-group label {
  margin-bottom: 8px;
  color: #555;
  font-weight: 500;
}

.date-input {
  padding: 12px;
  border: 2px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.date-input:focus {
  outline: none;
  border-color: #667eea;
}

.error-message {
  color: #e74c3c;
  background-color: #fdf2f2;
  padding: 12px;
  border-radius: 6px;
  border: 1px solid #e74c3c;
  margin-bottom: 20px;
  text-align: center;
}

.booking-summary {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  padding: 8px 0;
}

.summary-item.total {
  border-top: 2px solid #667eea;
  margin-top: 15px;
  padding-top: 15px;
  font-weight: bold;
  font-size: 18px;
  color: #667eea;
}

@media (max-width: 768px) {
  .date-inputs {
    grid-template-columns: 1fr;
  }
}
</style>

