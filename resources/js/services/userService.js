import api from './api'

export default {
  get(page = 1) {
    return api.get(`/users?page=${page}`)
  }
}