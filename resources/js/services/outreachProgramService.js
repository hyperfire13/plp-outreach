import api from './api'

export default {
  get(page = 1) {
    return api.get(`/outreach-programs?page=${page}`)
  },

  all() {
    return api.get('/outreach-programs/all')
  },

  store(data) {
    return api.post('/outreach-programs', data)
  },

  update(id, data) {
    return api.put(`/outreach-programs/${id}`, data)
  },

  delete(id) {
    return api.delete(`/outreach-programs/${id}`)
  }
}
