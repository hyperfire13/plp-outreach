import api from "@/api/axios";

const RESOURCE = "/outreach-projects";

export default {
    async paginate(params = {}) {
        const response = await api.get(RESOURCE, { params });

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
        const response = await api.put(
            `${RESOURCE}/${id}`,
            payload,
        );

        return response.data;
    },

    async remove(id) {
        const response = await api.delete(
            `${RESOURCE}/${id}`,
        );

        return response.data;
    },

    async submit(id) {
        const response = await api.post(
            `${RESOURCE}/${id}/submit`,
        );

        return response.data;
    },

    async approve(id, payload = {}) {
        const response = await api.post(
            `${RESOURCE}/${id}/approve`,
            payload,
        );

        return response.data;
    },

    async reject(id, payload) {
        const response = await api.post(
            `${RESOURCE}/${id}/reject`,
            payload,
        );

        return response.data;
    },
};
