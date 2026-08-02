import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { ROLES } from "@/constants/roles";

export function useAuthorization() {
    const authStore = useAuthStore();

    const roleName = computed(() => {
        return (
            authStore.user?.role?.name ??
            authStore.user?.role_name ??
            null
        );
    });

    const hasRole = (...roles) => {
        return roles.includes(roleName.value);
    };

    const canAccessRoles = (roles = []) => {
        return roles.length === 0 || hasRole(...roles);
    };

    const isAdministrator = computed(() =>
        hasRole(
            ROLES.SUPER_ADMIN,
            ROLES.CALO_ADMINISTRATOR,
        ),
    );

    const canManageProgramCatalog = computed(
        () => isAdministrator.value,
    );

    const canCreateProject = computed(() =>
        hasRole(
            ROLES.SUPER_ADMIN,
            ROLES.CALO_ADMINISTRATOR,
            ROLES.PROJECT_PROPONENT,
            ROLES.FACULTY_EXTENSION_COORDINATOR,
        ),
    );

    const canViewAllProjects = computed(() =>
        hasRole(
            ROLES.SUPER_ADMIN,
            ROLES.CALO_ADMINISTRATOR,
            ROLES.MONITORING_EVALUATION_TEAM,
        ),
    );

    return {
        roleName,
        hasRole,
        canAccessRoles,
        isAdministrator,
        canManageProgramCatalog,
        canCreateProject,
        canViewAllProjects,
    };
}
