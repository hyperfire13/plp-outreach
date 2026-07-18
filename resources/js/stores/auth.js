import { defineStore } from "pinia";
import authService from "../services/authService";
import router from "../router";

const TOKEN_KEY = "token";
const USER_KEY = "auth_user";

const getStoredUser = () => {
    try {
        const value = localStorage.getItem(USER_KEY);

        return value ? JSON.parse(value) : null;
    } catch {
        localStorage.removeItem(USER_KEY);
        return null;
    }
};

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: getStoredUser(),
        token: localStorage.getItem(TOKEN_KEY) || null,
        initialized: false,
        authenticating: false,
    }),

    getters: {
        isAuthenticated: (state) => Boolean(state.token),

        role: (state) =>
            state.user?.role?.name ??
            state.user?.role_name ??
            null,

        userId: (state) => state.user?.id ?? null,

        collegeId: (state) =>
            state.user?.college_id ??
            state.user?.college?.id ??
            null,
    },

    actions: {
        setSession({ user, token }) {
            this.user = user;
            this.token = token;

            localStorage.setItem(TOKEN_KEY, token);
            localStorage.setItem(
                USER_KEY,
                JSON.stringify(user),
            );
        },

        setUser(user) {
            this.user = user;

            if (user) {
                localStorage.setItem(
                    USER_KEY,
                    JSON.stringify(user),
                );
            } else {
                localStorage.removeItem(USER_KEY);
            }
        },

        clearSession() {
            this.user = null;
            this.token = null;

            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
        },

        async login(credentials) {
            this.authenticating = true;

            try {
                const response =
                    await authService.login(credentials);

                const payload = response.data;

                this.setSession({
                    user: payload.user,
                    token: payload.token,
                });

                const redirect =
                    router.currentRoute.value.query
                        .redirect;

                await router.replace(
                    typeof redirect === "string"
                        ? redirect
                        : "/",
                );

                return payload;
            } finally {
                this.authenticating = false;
            }
        },

        async register(data) {
            this.authenticating = true;

            try {
                const response =
                    await authService.register(data);

                const payload = response.data;

                this.setSession({
                    user: payload.user,
                    token: payload.token,
                });

                await router.replace("/");

                return payload;
            } finally {
                this.authenticating = false;
            }
        },

        async fetchUser() {
            if (!this.token) {
                this.initialized = true;
                return null;
            }

            try {
                const response = await authService.me();

                /*
                 * Supports either:
                 *
                 * { user: {...} }
                 *
                 * or:
                 *
                 * {...userFields}
                 */
                const user =
                    response.data.user ??
                    response.data;

                this.setUser(user);

                return user;
            } catch (error) {
                const status = error.response?.status;

                if (status === 401) {
                    this.clearSession();
                }

                throw error;
            } finally {
                this.initialized = true;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await authService.logout();
                }
            } catch (error) {
                /*
                 * Clear the local session even if the token is
                 * already expired or the logout request fails.
                 */
                console.error(
                    "Logout request failed:",
                    error,
                );
            } finally {
                this.clearSession();

                if (
                    router.currentRoute.value.name !==
                    "login"
                ) {
                    await router.replace({
                        name: "login",
                    });
                }
            }
        },

        forceLogout() {
            this.clearSession();

            if (
                router.currentRoute.value.name !== "login"
            ) {
                router.replace({
                    name: "login",
                });
            }
        },

        hasRole(...roles) {
            return roles.includes(this.role);
        },
    },
});
