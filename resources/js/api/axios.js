import axios from "axios";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || "/api/v1",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
    timeout: 30000,
});

api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("token");

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },
    (error) => Promise.reject(error),
);

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;

        if (status === 401) {
            localStorage.removeItem("token");
            localStorage.removeItem("auth_user");

            if (window.location.pathname !== "/login") {
                window.location.href = "/login";
            }
        }

        return Promise.reject(error);
    },
);

export default api;
