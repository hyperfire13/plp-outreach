import api from "@/api/axios";

const endpoint = "/v1/priority-needs";

export default {

    getList(params = {}) {
        return api.get(endpoint, { params });
    },

    getSummary(params = {}) {
        return api.get(`${endpoint}/summary`, {
            params
        });
    }

}
