import api from "@/api/axios";
const endpoint = "/survey-questions";

export default {

    getList(params = {}) {
        return api.get(endpoint, { params });
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

    remove(id) {
        return api.delete(`${endpoint}/${id}`);
    }

}
