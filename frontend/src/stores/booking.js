import { defineStore } from 'pinia'

// Helper functions for localStorage
const loadFromStorage = (key, defaultValue) => {
  try {
    const stored = localStorage.getItem(`booking_${key}`)
    return stored ? JSON.parse(stored) : defaultValue
  } catch (error) {
    console.error(`Error loading ${key} from localStorage:`, error)
    return defaultValue
  }
}

const saveToStorage = (key, value) => {
  try {
    localStorage.setItem(`booking_${key}`, JSON.stringify(value))
  } catch (error) {
    console.error(`Error saving ${key} to localStorage:`, error)
  }
}

export const useBookingStore = defineStore('booking', {
  state: () => ({
    currentStep: loadFromStorage('currentStep', 1),
    selectedRoom: loadFromStorage('selectedRoom', null),
    checkInDate: loadFromStorage('checkInDate', null),
    checkOutDate: loadFromStorage('checkOutDate', null),
    guestCount: loadFromStorage('guestCount', 2),
    searchParams: loadFromStorage('searchParams', {
      checkin: null,
      checkout: null,
      guests: 2,
      destination: ''
    }),
    contactInfo: loadFromStorage('contactInfo', {
      title: 'Mr.',
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      address: '',
      city: '',
      state: '',
      zipCode: '',
      specialRequests: ''
    }),
    guestInfo: {
      fullName: '',
      email: '',
      phone: ''
    },
    rooms: [
      {
        id: 1,
        name: 'Standard Room',
        price: 99,
        description: 'Comfortable room with queen bed, private bathroom, and city view.',
        image: '/api/placeholder/300/200'
      },
      {
        id: 2,
        name: 'Deluxe Room',
        price: 149,
        description: 'Spacious room with king bed, sitting area, and premium amenities.',
        image: '/api/placeholder/300/200'
      },
      {
        id: 3,
        name: 'Suite',
        price: 249,
        description: 'Luxurious suite with separate living room, kitchenette, and balcony.',
        image: '/api/placeholder/300/200'
      }
    ]
  }),

  getters: {
    totalNights() {
      if (!this.checkInDate || !this.checkOutDate) return 0
      const diffTime = new Date(this.checkOutDate) - new Date(this.checkInDate)
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    },

    totalPrice() {
      if (!this.selectedRoom) return 0
      return this.totalNights * this.selectedRoom.price
    },

    canProceedToNextStep() {
      switch (this.currentStep) {
        case 1:
          return this.selectedRoom !== null
        case 2:
          return this.checkInDate && this.checkOutDate &&
                 new Date(this.checkOutDate) > new Date(this.checkInDate)
        case 3:
          return this.guestInfo.fullName && this.guestInfo.email && this.guestInfo.phone
        default:
          return false
      }
    }
  },

  actions: {
    selectRoom(room) {
      this.selectedRoom = room
      saveToStorage('selectedRoom', room)
    },

    setSelectedRoom(room) {
      this.selectedRoom = room
      saveToStorage('selectedRoom', room)
    },

    setDates(checkIn, checkOut) {
      this.checkInDate = checkIn
      this.checkOutDate = checkOut
      saveToStorage('checkInDate', checkIn)
      saveToStorage('checkOutDate', checkOut)
    },

    setBookingDates(checkIn, checkOut) {
      this.checkInDate = checkIn
      this.checkOutDate = checkOut
      // Also update searchParams for consistency
      this.searchParams.checkin = checkIn
      this.searchParams.checkout = checkOut
      
      // Save to localStorage
      saveToStorage('checkInDate', checkIn)
      saveToStorage('checkOutDate', checkOut)
      saveToStorage('searchParams', this.searchParams)
    },

    setGuestCount(count) {
      this.guestCount = count
      this.searchParams.guests = count
      saveToStorage('guestCount', count)
      saveToStorage('searchParams', this.searchParams)
    },

    setSearchParams(params) {
      this.searchParams = { ...this.searchParams, ...params }
      saveToStorage('searchParams', this.searchParams)
    },

    setContactInfo(info) {
      this.contactInfo = { ...this.contactInfo, ...info }
      saveToStorage('contactInfo', this.contactInfo)
    },

    setGuestInfo(info) {
      this.guestInfo = { ...this.guestInfo, ...info }
    },

    nextStep() {
      if (this.canProceedToNextStep && this.currentStep < 4) {
        this.currentStep++
        saveToStorage('currentStep', this.currentStep)
      }
    },

    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--
        saveToStorage('currentStep', this.currentStep)
      }
    },

    resetBooking() {
      this.currentStep = 1
      this.selectedRoom = null
      this.checkInDate = null
      this.checkOutDate = null
      this.guestCount = 2
      this.searchParams = {
        checkin: null,
        checkout: null,
        guests: 2,
        destination: ''
      }
      this.contactInfo = {
        title: 'Mr.',
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        zipCode: '',
        specialRequests: ''
      }
      this.guestInfo = {
        fullName: '',
        email: '',
        phone: ''
      }
      
      // Clear localStorage
      localStorage.removeItem('booking_currentStep')
      localStorage.removeItem('booking_selectedRoom')
      localStorage.removeItem('booking_checkInDate')
      localStorage.removeItem('booking_checkOutDate')
      localStorage.removeItem('booking_guestCount')
      localStorage.removeItem('booking_searchParams')
      localStorage.removeItem('booking_contactInfo')
    }
  }
})
