import api from './api'

export default {
  get(page = 1) {
    return api.get(`/colleges?page=${page}`)
  },
  all() {
    return api.get('/colleges/all')
  },

  store(data) {
    return api.post('/colleges', data)
  },
  update(id, data) {
    return api.put(`/colleges/${id}`, data)
  },
  delete(id) {
    return api.delete(`/colleges/${id}`)
  }
}
