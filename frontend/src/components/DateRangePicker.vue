<template>
  <div class="date-range-picker">
    <div class="date-field" @click="togglePicker">
      <label class="date-label">
        <span class="date-icon"><i class="pi pi-calendar"></i></span>
        Check-in — Check-out
      </label>
      <div class="date-display">
        <span class="date-value">{{ displayText }}</span>
      </div>
    </div>
    
    <!-- Calendar Dropdown -->
    <div v-if="showPicker" class="calendar-dropdown" ref="calendarDropdown">
      <div class="calendar-header">
        <button type="button" @click="previousMonth" class="nav-btn">‹</button>
        <span class="month-year">{{ formatMonthYear(currentMonth) }}</span>
        <button type="button" @click="nextMonth" class="nav-btn">›</button>
      </div>
      
      <div class="calendar-grid">
        <div class="weekdays">
          <div v-for="day in weekdays" :key="day" class="weekday">{{ day }}</div>
        </div>
        
        <div class="days-grid">
          <div
            v-for="day in calendarDays"
            :key="day.date"
            @click="selectDate(day)"
            :class="getDayClasses(day)"
          >
            {{ day.day }}
          </div>
        </div>
      </div>
      
      <div class="calendar-footer">
        <button type="button" @click="clearDates" class="clear-btn">Clear</button>
        <button type="button" @click="closePicker" class="done-btn">Done</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'DateRangePicker',
  props: {
    checkin: {
      type: String,
      default: ''
    },
    checkout: {
      type: String,
      default: ''
    }
  },
  emits: ['update:checkin', 'update:checkout'],
  setup(props, { emit }) {
    const showPicker = ref(false)
    const currentMonth = ref(new Date())
    const selectingCheckin = ref(true)
    const calendarDropdown = ref(null)
    
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    
    const calendarDays = computed(() => {
      const year = currentMonth.value.getFullYear()
      const month = currentMonth.value.getMonth()
      const firstDay = new Date(year, month, 1)
      const startDate = new Date(firstDay)
      startDate.setDate(startDate.getDate() - firstDay.getDay())
      
      const days = []
      
      for (let i = 0; i < 42; i++) {
        const date = new Date(startDate)
        date.setDate(startDate.getDate() + i)
        
        const dateStr = date.toISOString().split('T')[0]
        const isCurrentMonth = date.getMonth() === month
        const isPastDate = date < today
        
        days.push({
          date: dateStr,
          day: date.getDate(),
          isCurrentMonth,
          isPastDate,
          disabled: isPastDate,
          isNextMonth: date.getMonth() > month,
          isPrevMonth: date.getMonth() < month
        })
      }
      
      return days
    })
    
    const displayText = computed(() => {
      if (!props.checkin && !props.checkout) {
        return 'Select dates'
      }
      
      if (props.checkin && !props.checkout) {
        const checkinDate = new Date(props.checkin)
        const checkinStr = checkinDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        return `${checkinStr} — Select checkout`
      }
      
      if (props.checkin && props.checkout) {
        const checkinDate = new Date(props.checkin)
        const checkoutDate = new Date(props.checkout)
        const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24))
        
        const checkinStr = checkinDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        const checkoutStr = checkoutDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        
        return `${checkinStr} — ${checkoutStr} (${nights} night${nights > 1 ? 's' : ''})`
      }
      
      return 'Select dates'
    })
    
    const formatMonthYear = (date) => {
      return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    }
    
    const getDayClasses = (day) => {
      const classes = ['day-cell']
      
      if (day.disabled) {
        classes.push('day-cell--disabled')
      }
      
      if (!day.isCurrentMonth) {
        classes.push('day-cell--other-month')
      }
      
      if (day.date === props.checkin) {
        classes.push('day-cell--start')
      }
      
      if (day.date === props.checkout) {
        classes.push('day-cell--end')
      }
      
      if (props.checkin && props.checkout && day.date > props.checkin && day.date < props.checkout) {
        classes.push('day-cell--in-range')
      }
      
      if (day.date === today.toISOString().split('T')[0]) {
        classes.push('day-cell--today')
      }
      
      return classes
    }
    
    const togglePicker = () => {
      showPicker.value = !showPicker.value
      if (showPicker.value) {
        selectingCheckin.value = true
      }
    }
    
    const closePicker = () => {
      showPicker.value = false
    }
    
    const previousMonth = () => {
      const newMonth = new Date(currentMonth.value)
      newMonth.setMonth(newMonth.getMonth() - 1)
      currentMonth.value = newMonth
    }
    
    const nextMonth = () => {
      const newMonth = new Date(currentMonth.value)
      newMonth.setMonth(newMonth.getMonth() + 1)
      currentMonth.value = newMonth
    }
    
    const selectDate = (day) => {
      if (day.disabled) return
      
      if (selectingCheckin.value || !props.checkin) {
        emit('update:checkin', day.date)
        emit('update:checkout', '')
        selectingCheckin.value = false
      } else {
        const checkinDate = new Date(props.checkin)
        const selectedDate = new Date(day.date)
        
        if (selectedDate <= checkinDate) {
          emit('update:checkin', day.date)
          emit('update:checkout', '')
          selectingCheckin.value = false
        } else {
          emit('update:checkout', day.date)
          selectingCheckin.value = true
        }
      }
    }
    
    const clearDates = () => {
      emit('update:checkin', '')
      emit('update:checkout', '')
      selectingCheckin.value = true
    }
    
    const handleClickOutside = (event) => {
      if (calendarDropdown.value && !calendarDropdown.value.contains(event.target) && !event.target.closest('.date-range-picker')) {
        closePicker()
      }
    }
    
    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })
    
    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })
    
    return {
      showPicker,
      currentMonth,
      weekdays,
      calendarDays,
      displayText,
      calendarDropdown,
      formatMonthYear,
      getDayClasses,
      togglePicker,
      closePicker,
      previousMonth,
      nextMonth,
      selectDate,
      clearDates
    }
  }
}
</script>

<style scoped>
.date-range-picker {
  flex: 2;
  border-right: 1px solid #e0e0e0;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  cursor: pointer;
}

.date-field {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.date-label {
  font-size: 12px;
  font-weight: 600;
  color: #003580;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.date-icon {
  font-size: 14px;
}

.date-display {
  display: flex;
  align-items: center;
}

.date-value {
  font-size: 14px;
  color: #333;
}

.calendar-dropdown {
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
  min-width: 300px;
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.nav-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  color: #0071c2;
  transition: background 0.2s ease;
}

.nav-btn:hover {
  background: #f0f0f0;
}

.month-year {
  font-weight: 600;
  color: #333;
}

.calendar-grid {
  margin-bottom: 16px;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  margin-bottom: 8px;
}

.weekday {
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #666;
  padding: 8px 4px;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
}

.day-cell {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s ease;
  color: #333;
  min-height: 36px;
}

.day-cell:hover:not(.day-cell--disabled) {
  background: #e6f3ff;
}

.day-cell--disabled {
  color: #ccc;
  cursor: not-allowed;
}

.day-cell--other-month {
  color: #999;
  opacity: 0.6;
}

.day-cell--other-month:hover:not(.day-cell--disabled) {
  background: #f0f8ff;
  opacity: 1;
}

.day-cell--start,
.day-cell--end {
  background: #0071c2;
  color: white;
}

.day-cell--in-range {
  background: #e6f3ff;
  color: #0071c2;
}

.day-cell--today {
  font-weight: 600;
  border: 2px solid #0071c2;
}

.calendar-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #f0f0f0;
  padding-top: 12px;
}

.clear-btn {
  background: none;
  border: none;
  color: #0071c2;
  font-size: 14px;
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 4px;
  transition: background 0.2s ease;
}

.clear-btn:hover {
  background: #f0f0f0;
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
  .date-range-picker {
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
    padding: 16px;
  }

  .calendar-dropdown {
    left: -16px;
    right: -16px;
    min-width: auto;
  }
}
</style> 