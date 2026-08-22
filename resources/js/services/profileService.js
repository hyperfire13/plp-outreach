import api from "@/services/api";

const RESOURCE = "/profile";

export default {
    async get() {
        const response = await api.get(RESOURCE);
        return response.data;
    },

    async update(payload) {
        const response = await api.put(RESOURCE, payload);
        return response.data;
    },

    async updatePassword(payload) {
        const response = await api.put(`${RESOURCE}/password`, payload);
        return response.data;
    },
};
