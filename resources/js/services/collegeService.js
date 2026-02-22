import api from './api'

export default {
  get(page = 1) {
    return api.get(`/colleges?page=${page}`)
  }
}