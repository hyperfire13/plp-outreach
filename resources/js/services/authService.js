import api from './api'

export default {
  login(data) {
    return api.post('/login', data)
  },
  register(data) {
    return api.post('/register', data)
  },
  logout() {
    return api.post('/logout')
  }
}