import api from "@/api/axios";
export default {
    async paginate(params = {}) {
        return (await api.get("/audit-logs", { params })).data;
    },
};
