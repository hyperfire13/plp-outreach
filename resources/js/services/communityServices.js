import api from './api'

const endpoint = "/communities";

export default {

    getList(params = {}) {
        return api.get(endpoint, { params });
    },

    getAll() {
        return api.get(`${endpoint}/all`);
    },

    get(id) {
        return api.get(`${endpoint}/${id}`);
    },

    create(data) {
        return api.post(endpoint, data);
    },

    update(id, data) {
        return api.put(`${endpoint}/${id}`, data);
    },

    delete(id) {
        return api.delete(`${endpoint}/${id}`);
    }

}
