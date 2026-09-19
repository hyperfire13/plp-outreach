import api from "@/api/axios";

const endpoint = "/priority-needs";

export default {
    getList(params = {}) {
        return api.get(endpoint, { params });
    },

    getSummary(params = {}) {
        return api.get(`${endpoint}/summary`, {
            params,
        });
    },
    validate(id, remarks = null) {
        return api.post(`${endpoint}/${id}/validate`, { remarks });
    },
    reject(id, remarks) {
        return api.post(`${endpoint}/${id}/reject`, { remarks });
    },
};
