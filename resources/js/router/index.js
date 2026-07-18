import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

import Login from "../views/Login.vue";
import Dashboard from "../views/Dashboard.vue";
import Users from "../views/Users.vue";
import Colleges from "../views/Colleges.vue";

const routes = [
    {
        path: "/login",
        name: "login",
        component: Login,
    },
    {
        path: "/",
        name: "dashboard",
        component: Dashboard,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: "/users",
        name: "users",
        component: Users,
        meta: {
            requiresAuth: true,
            roles: [
                "super_admin",
                "college_admin",
            ],
        },
    },
    {
        path: "/colleges",
        name: "colleges",
        component: Colleges,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: "/outreach-programs",
        name: "outreach-programs",
        component: () =>
            import("../pages/OutreachPrograms.vue"),
        meta: {
            requiresAuth: true,
            roles: [
                "super_admin",
                "calo_administrator",
            ],
        },
    },
    {
        path: "/outreach-projects",
        name: "outreach-projects",
        component: () =>
            import("../pages/OutreachProjects.vue"),
        meta: {
            requiresAuth: true,
            roles: [
                "super_admin",
                "calo_administrator",
                "college_admin",
                "project_proponent",
                "faculty_extension_coordinator",
                "college_department_head",
                "community_partner",
                "external_evaluator",
                "student_volunteer",
                "alumni_partner",
                "monitoring_evaluation_team",
            ],
        },
    },
    {
        path: "/forbidden",
        name: "forbidden",
        component: () =>
            import("../pages/Forbidden.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    /*
     * Do not call /me unnecessarily when visiting
     * a public route without a token.
     */
    if (
        !authStore.initialized &&
        authStore.token
    ) {
        try {
            await authStore.fetchUser();
        } catch {
            // fetchUser should already clear invalid sessions.
        }
    }

    /*
     * Mark initialization complete when no token exists.
     */
    if (!authStore.token) {
        authStore.initialized = true;
    }

    if (
        to.meta.requiresAuth &&
        !authStore.isAuthenticated
    ) {
        return {
            name: "login",
            query: {
                redirect: to.fullPath,
            },
        };
    }

    if (
        Array.isArray(to.meta.roles) &&
        to.meta.roles.length > 0
    ) {
        const userRole = authStore.role;

        if (
            !userRole ||
            !to.meta.roles.includes(userRole)
        ) {
            return {
                name: "forbidden",
            };
        }
    }

    if (
        to.name === "login" &&
        authStore.isAuthenticated
    ) {
        return {
            name: "dashboard",
        };
    }

    return true;
});

export default router;
