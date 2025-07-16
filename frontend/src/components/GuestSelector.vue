<template>
  <div class="guest-selector">
    <div class="guest-field" @click="toggleDropdown">
      <label class="guest-label">
        <span class="guest-icon"><i class="pi pi-users"></i></span>
        Guests
      </label>
      <div class="guest-display">
        <div class="guest-summary">{{ guestSummary }}</div>
        <span class="dropdown-arrow" :class="{ 'dropdown-arrow--open': showDropdown }">▼</span>
      </div>
    </div>
    
    <div v-if="showDropdown" class="guests-dropdown" ref="guestDropdown">
      <div class="guest-row">
        <div class="guest-info">
          <div class="guest-type">Guests</div>
          <div class="guest-description">Number of guests</div>
        </div>
        <div class="guest-counter">
          <button type="button" @click="decrementGuests" :disabled="guests <= 1" class="counter-btn">-</button>
          <span class="counter-value">{{ guests }}</span>
          <button type="button" @click="incrementGuests" :disabled="guests >= 16" class="counter-btn">+</button>
        </div>
      </div>
      
      <div class="dropdown-footer">
        <button type="button" @click="closeDropdown" class="done-btn">Done</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'GuestSelector',
  props: {
    guests: {
      type: Number,
      default: 2
    }
  },
  emits: ['update:guests'],
  setup(props, { emit }) {
    const showDropdown = ref(false)
    const guestDropdown = ref(null)
    
    const guestSummary = computed(() => {
      return `${props.guests} guest${props.guests > 1 ? 's' : ''}`
    })
    
    const toggleDropdown = () => {
      showDropdown.value = !showDropdown.value
    }
    
    const closeDropdown = () => {
      showDropdown.value = false
    }
    
    const incrementGuests = () => {
      if (props.guests < 16) {
        emit('update:guests', props.guests + 1)
      }
    }
    
    const decrementGuests = () => {
      if (props.guests > 1) {
        emit('update:guests', props.guests - 1)
      }
    }
    
    // Click outside handler
    const handleClickOutside = (event) => {
      if (guestDropdown.value && !guestDropdown.value.contains(event.target) && !event.target.closest('.guest-selector')) {
        closeDropdown()
      }
    }
    
    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })
    
    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })
    
    return {
      showDropdown,
      guestDropdown,
      guestSummary,
      toggleDropdown,
      closeDropdown,
      incrementGuests,
      decrementGuests
    }
  }
}
</script>

<style scoped>
.guest-selector {
  flex: 1.5;
  border-right: 1px solid #e0e0e0;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  cursor: pointer;
}

.guest-field {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.guest-label {
  font-size: 12px;
  font-weight: 600;
  color: #003580;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.guest-icon {
  font-size: 14px;
}

.guest-display {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
}

.guest-summary {
  font-size: 14px;
  color: #333;
}

.dropdown-arrow {
  font-size: 10px;
  color: #666;
  transition: transform 0.2s ease;
}

.dropdown-arrow--open {
  transform: rotate(180deg);
}

.guests-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  padding: 16px;
  margin-top: 4px;
}

.guest-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.guest-row:last-of-type {
  border-bottom: none;
}

.guest-info {
  flex: 1;
}

.guest-type {
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.guest-description {
  font-size: 12px;
  color: #666;
  margin-top: 2px;
}

.guest-counter {
  display: flex;
  align-items: center;
  gap: 12px;
}

.counter-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #0071c2;
  background: white;
  color: #0071c2;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.counter-btn:hover:not(:disabled) {
  background: #0071c2;
  color: white;
}

.counter-btn:disabled {
  border-color: #ccc;
  color: #ccc;
  cursor: not-allowed;
}

.counter-value {
  font-weight: 600;
  color: #333;
  min-width: 20px;
  text-align: center;
}

.dropdown-footer {
  text-align: right;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
  margin-top: 12px;
}

.done-btn {
  background: #0071c2;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.done-btn:hover {
  background: #005999;
}

@media (max-width: 768px) {
  .guest-selector {
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
    padding: 16px;
  }

  .guests-dropdown {
    left: -16px;
    right: -16px;
  }
}
</style> 