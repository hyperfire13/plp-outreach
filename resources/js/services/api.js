import axios from 'axios'
import { useLoadingStore } from '../stores/loading'

const api = axios.create({
  baseURL: '/api/v1',
  withCredentials: true
})

api.interceptors.request.use(config => {
  const loading = useLoadingStore()
  loading.start()
  return config
})

api.interceptors.response.use(
  response => {
    const loading = useLoadingStore()
    loading.stop()
    return response
  },
  error => {
    const loading = useLoadingStore()
    loading.stop()
    return Promise.reject(error)
  }
)

export default api