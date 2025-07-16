import api from './index.js'

export const bookingsAPI = {
  getBookings: async (params = {}) => {
    const response = await api.get('/bookings', { params })
    return response.data
  },

  getBooking: async (id) => {
    const response = await api.get(`/bookings/${id}`)
    return response.data
  },

  createBooking: async (bookingData) => {
    const response = await api.post('/bookings', bookingData)
    return response.data
  },

  updateBooking: async (id, bookingData) => {
    const response = await api.put(`/bookings/${id}`, bookingData)
    return response.data
  },

  cancelBooking: async (id) => {
    const response = await api.post(`/bookings/${id}/cancel`)
    return response.data
  },

  searchRooms: async (searchParams) => {
    const response = await api.get('/rooms/search', { params: searchParams })
    return response.data
  },

  getRoomDetails: async (id) => {
    const response = await api.get(`/rooms/${id}`)
    return response.data
  },

  getRooms: async () => {
    const response = await api.get('/rooms')
    return response.data
  },

  getRoomSuggestions: async (query, limit = 10) => {
    const response = await api.get('/rooms/suggestions', { 
      params: { query, limit } 
    })
    return response.data
  }
}
