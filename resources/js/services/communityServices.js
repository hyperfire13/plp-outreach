import api from './api'

export default {
  get(page = 1, params = {}) {
    return api.get('/communities', {
      params: { page, ...params }
    })
  },

  store(data) {
    return api.post('/communities', data)
  },

  update(id, data) {
    return api.put(`/communities/${id}`, data)
  },

  delete(id) {
    return api.delete(`/communities/${id}`)
  }
}