import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import { useLoadingStore } from '../stores/loading'

const api = axios.create({
  baseURL: '/api/v1'
})

// REQUEST INTERCEPTOR
api.interceptors.request.use(config => {
  const auth = useAuthStore()
  const loading = useLoadingStore()

  loading.start()

  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }

  return config
})

// RESPONSE INTERCEPTOR
api.interceptors.response.use(
  response => {
    const loading = useLoadingStore()
    loading.stop()
    return response
  },
  error => {
    const loading = useLoadingStore()
    const auth = useAuthStore()

    loading.stop()

    if (error.response && error.response.status === 401) {
      auth.forceLogout()
    }

    return Promise.reject(error)
  }
)

export default api
