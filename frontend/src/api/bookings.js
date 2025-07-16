import api from './index.js'

export const bookingsAPI = {
  // Get all bookings for current user
  getBookings: async () => {
    const response = await api.get('/bookings')
    return response.data
  },

  // Get booking by ID
  getBooking: async (id) => {
    const response = await api.get(`/bookings/${id}`)
    return response.data
  },

  // Create new booking
  createBooking: async (bookingData) => {
    const response = await api.post('/bookings', bookingData)
    return response.data
  },

  // Update booking
  updateBooking: async (id, bookingData) => {
    const response = await api.put(`/bookings/${id}`, bookingData)
    return response.data
  },

  // Cancel booking
  cancelBooking: async (id) => {
    const response = await api.delete(`/bookings/${id}`)
    return response.data
  },

  // Search available rooms
  searchRooms: async (searchParams) => {
    const response = await api.get('/rooms/search', { params: searchParams })
    return response.data
  },

  // Get room details
  getRoomDetails: async (id) => {
    const response = await api.get(`/rooms/${id}`)
    return response.data
  },

  // Get all rooms
  getRooms: async () => {
    const response = await api.get('/rooms')
    return response.data
  }
}
