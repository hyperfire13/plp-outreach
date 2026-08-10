import api from "@/api/axios";

const RESOURCE = "/engagement-records";

export default {
    async paginate(params = {}) {
        const response = await api.get(RESOURCE, { params });
        return response.data;
    },

    async options() {
        const response = await api.get(`${RESOURCE}/options`);
        return response.data;
    },

    async find(id) {
        const response = await api.get(`${RESOURCE}/${id}`);
        return response.data;
    },

    async store(payload) {
        const response = await api.post(RESOURCE, payload);
        return response.data;
    },

    async update(id, payload) {
        const response = await api.put(`${RESOURCE}/${id}`, payload);
        return response.data;
    },

    async remove(id) {
        const response = await api.delete(`${RESOURCE}/${id}`);
        return response.data;
    },

    async submit(id) {
        const response = await api.post(`${RESOURCE}/${id}/submit`);
        return response.data;
    },

    async approve(id) {
        const response = await api.post(`${RESOURCE}/${id}/approve`);
        return response.data;
    },

    async reject(id, validationRemarks) {
        const response = await api.post(`${RESOURCE}/${id}/reject`, {
            validation_remarks: validationRemarks,
        });
        return response.data;
    },

    async myProfile(params = {}) {
        const response = await api.get("/engagement-profiles/me", { params });
        return response.data;
    },

    async userProfile(userId, params = {}) {
        const response = await api.get(`/engagement-profiles/${userId}`, {
            params,
        });
        return response.data;
    },
};
