import api from './api'

export default {
  get(page = 1, params = {}) {
    return api.get('/users', {
      params: {
        page,
        ...params
      }
    })
  },

  store(data) {
    return api.post('/users', data)
  },

  update(id, data) {
    return api.put(`/users/${id}`, data)
  },

  delete(id) {
    return api.delete(`/users/${id}`)
  },

  show(id) {
    return api.get(`/users/${id}`)
  },
  async all(params = {}) {
    const response = await api.get("/users/all", {
        params,
    });

    return response.data;
  }
}
