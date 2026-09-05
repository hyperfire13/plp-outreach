import api from "@/api/axios";

export default {
    async get() {
        const response = await api.get("/dashboard");
        return response.data;
    },
};
