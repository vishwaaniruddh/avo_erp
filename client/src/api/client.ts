import axios from 'axios'

export const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request Interceptor: attach JWT
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('avo_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response Interceptor: handle standard HTTP status codes
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    const status = error.response?.status
    const message = error.response?.data?.message || error.message || 'An unexpected error occurred'
    const errors = error.response?.data?.errors

    if (status === 401) {
      // Clear token on 401 Unauthorized
      localStorage.removeItem('avo_token')
      localStorage.removeItem('avo_user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }

    return Promise.reject({
      status,
      message,
      errors,
      raw: error,
    })
  }
)

