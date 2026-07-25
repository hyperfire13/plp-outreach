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
  path: "/communities",
  name: "admin-communities",
  component: () =>
    import("@/views/admin/Communities.vue"),
  meta: {
    title: "Communities",
    requiresAuth: true,
  },
    },
    {
    path: "/survey-templates",
    name: "admin-survey-templates",
    component: () =>
        import("@/views/admin/SurveyTemplates.vue"),
    meta: {
        title: "Survey Templates",
        requiresAuth: true,
    },
    },
    {
    path: "/survey-questions",
    name: "admin-survey-questions",
    component: () =>
        import("@/views/admin/SurveyQuestions.vue"),
    meta: {
        title: "Survey Questions",
        requiresAuth: true,
    },
    },
    {
    path: "/survey-responses",
    name: "admin-survey-responses",
    component: () =>
        import("@/views/admin/SurveyResponses.vue"),
    meta: {
        title: "Survey Responses",
        requiresAuth: true,
    },
    },
    {
    path: "/survey-responses/create",
    name: "admin-survey-responses-create",
    component: () =>
        import("@/views/admin/SurveyResponseCreate.vue"),
    meta: {
        title: "Conduct Survey",
        requiresAuth: true,
    },
    },
    {
    path: "/survey-responses/:id/edit",
    name: "admin-survey-responses-edit",
    component: () =>
        import("@/views/admin/SurveyResponseCreate.vue"),
    meta: {
        title: "Edit Survey Response",
        requiresAuth: true,
    },
    },
    {
    path: "/survey-responses/:id",
    name: "admin-survey-responses-view",
    component: () =>
        import("@/views/admin/SurveyResponseView.vue"),
    meta: {
        title: "Survey Response",
        requiresAuth: true,
    },
    },
    {
    path: "/priority-needs",
    name: "admin-priority-needs",
    component: () =>
        import("@/views/admin/PriorityNeeds.vue"),
    meta: {
        title: "Priority Needs",
        requiresAuth: true,
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
