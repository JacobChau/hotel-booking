<template>
  <div class="room-selection">
    <h2 class="room-selection__title">Choose Your Room</h2>
    <div class="room-selection__grid">
      <div
        v-for="room in rooms"
        :key="room.id"
        class="room-card"
        :class="{ 'room-card--selected': selectedRoom?.id === room.id }"
        @click="selectRoom(room)"
      >
        <div class="room-card__image">
          <img :src="room.image" :alt="room.name" class="room-card__img" />
        </div>
        <div class="room-card__info">
          <h3 class="room-card__name">{{ room.name }}</h3>
          <p class="room-card__price">${{ room.price }} / night</p>
          <p class="room-card__description">{{ room.description }}</p>
        </div>
        <div class="room-card__indicator">
          <div v-if="selectedRoom?.id === room.id" class="room-card__check"><i class="pi pi-check"></i></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useBookingStore } from '../stores/booking'

export default {
  name: 'RoomSelection',
  setup() {
    const bookingStore = useBookingStore()

    const rooms = computed(() => bookingStore.rooms)
    const selectedRoom = computed(() => bookingStore.selectedRoom)

    const selectRoom = (room) => {
      bookingStore.selectRoom(room)
    }

    return {
      rooms,
      selectedRoom,
      selectRoom
    }
  }
}
</script>

<style scoped>
.room-selection__title {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.room-selection__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.room-card {
  border: 2px solid #ddd;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  background: white;
}

.room-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.room-card--selected {
  border-color: #667eea;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
}

.room-card__image {
  height: 200px;
  overflow: hidden;
}

.room-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.room-card__info {
  padding: 20px;
}

.room-card__name {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 20px;
}

.room-card__price {
  font-size: 18px;
  font-weight: bold;
  color: #667eea;
  margin: 0 0 15px 0;
}

.room-card__description {
  color: #666;
  line-height: 1.5;
  margin: 0;
}

.room-card__indicator {
  position: absolute;
  top: 15px;
  right: 15px;
}

.room-card__check {
  width: 30px;
  height: 30px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 16px;
}

@media (max-width: 768px) {
  .room-selection__grid {
    grid-template-columns: 1fr;
  }
}
</style>
