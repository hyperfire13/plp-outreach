import api from "@/api/axios";

const RESOURCE = "/project-application-templates";

export default {
    async paginate(params = {}) {
        return (await api.get(RESOURCE, { params })).data;
    },
    async store(payload) {
        return (await api.post(RESOURCE, payload)).data;
    },
    async update(id, payload) {
        return (await api.put(`${RESOURCE}/${id}`, payload)).data;
    },
    async remove(id) {
        return (await api.delete(`${RESOURCE}/${id}`)).data;
    },
};
