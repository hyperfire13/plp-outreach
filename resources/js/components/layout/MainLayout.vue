<template>
  <div class="app-wrapper">
    <div
      v-if="isMobile && isSidebarOpen"
      class="sidebar-overlay"
      @click="closeSidebar"
    />

    <nav class="app-header navbar navbar-expand bg-success">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a
              class="nav-link text-white"
              href="#"
              role="button"
              aria-label="Toggle sidebar"
              @click.prevent="toggleSidebar"
            >
              <i class="bi bi-list"></i>
            </a>
          </li>
          <li class="nav-item d-none d-md-block">
            <RouterLink :to="{ name: 'dashboard' }" class="nav-link text-white">
              Home
            </RouterLink>
          </li>
        </ul>

        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link text-white" href="#" data-lte-toggle="fullscreen">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i
                data-lte-icon="minimize"
                class="bi bi-fullscreen-exit"
                style="display: none"
              ></i>
            </a>
          </li>

          <li class="nav-item dropdown user-menu">
            <a
              href="#"
              class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-person-circle fs-5"></i>
              <span class="d-none d-md-inline">{{ currentUserName }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
              <li class="px-3 py-2 border-bottom">
                <div class="fw-semibold">{{ currentUserName }}</div>
                <small class="text-muted">{{ authStore.user?.email }}</small>
              </li>
              <li class="p-2">
                <button
                  type="button"
                  class="btn btn-outline-danger w-100"
                  :disabled="signingOut"
                  @click="signOut"
                >
                  <span
                    v-if="signingOut"
                    class="spinner-border spinner-border-sm me-1"
                  ></span>
                  {{ signingOut ? "Signing out..." : "Sign out" }}
                </button>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow">
      <div class="sidebar-brand bg-success text-white">
        <RouterLink :to="{ name: 'dashboard' }" class="brand-link">
          <span class="brand-text text-white">PLP Outreach</span>
        </RouterLink>
      </div>

      <div class="sidebar-wrapper bg-success text-primary">
        <nav class="mt-2">
          <ul
            id="navigation"
            class="nav sidebar-menu flex-column"
            role="navigation"
            aria-label="Main navigation"
          >
            <li class="nav-item">
              <RouterLink
                :to="{ name: 'dashboard' }"
                class="nav-link text-white"
                active-class="active"
                @click="closeSidebarOnMobile"
              >
                <i class="nav-icon bi bi-speedometer2"></i>
                <p>Dashboard</p>
              </RouterLink>
            </li>

            <li
              v-if="hasSettingsAccess"
              class="nav-item"
              :class="{ 'menu-open': settingsOpen }"
            >
              <a
                href="#"
                class="nav-link text-white"
                :class="{ active: isSettingsActive }"
                @click.prevent="settingsOpen = !settingsOpen"
              >
                <i class="nav-icon bi bi-gear"></i>
                <p>
                  Settings
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>

              <ul v-show="settingsOpen" class="nav nav-treeview">
                <li v-if="hasSystemSettingsAccess" class="nav-header text-white">
                  <b>SYSTEM SETTINGS</b>
                </li>
                <li v-if="canManageUsers" class="nav-item">
                  <RouterLink
                    :to="{ name: 'users' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-people"></i>
                    <p>Users</p>
                  </RouterLink>
                </li>
                <li v-if="canManageColleges" class="nav-item">
                  <RouterLink
                    :to="{ name: 'colleges' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-building"></i>
                    <p>Colleges</p>
                  </RouterLink>
                </li>

                <li v-if="hasOutreachAccess" class="nav-header text-white">
                  <b>OUTREACH MANAGEMENT</b>
                </li>
                <li v-if="canViewPrograms" class="nav-item">
                  <RouterLink
                    :to="{ name: 'outreach-programs' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-collection"></i>
                    <p>Outreach Programs</p>
                  </RouterLink>
                </li>
                <li v-if="canViewProjects" class="nav-item">
                  <RouterLink
                    :to="{ name: 'outreach-projects' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-briefcase"></i>
                    <p>Outreach Projects</p>
                  </RouterLink>
                </li>

                <li v-if="hasCommunityAssessmentAccess" class="nav-header text-white">
                  <b>COMMUNITY ASSESSMENT</b>
                </li>
                <li v-if="canManageCommunities" class="nav-item">
                  <RouterLink
                    :to="{ name: 'admin-communities' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-buildings"></i>
                    <p>Communities</p>
                  </RouterLink>
                </li>
                <li v-if="canDesignSurveys" class="nav-item">
                  <RouterLink
                    :to="{ name: 'admin-survey-templates' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-ui-checks-grid"></i>
                    <p>Survey Templates</p>
                  </RouterLink>
                </li>
                <li v-if="canDesignSurveys" class="nav-item">
                  <RouterLink
                    :to="{ name: 'admin-survey-questions' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-list-check"></i>
                    <p>Survey Questions</p>
                  </RouterLink>
                </li>
                <li v-if="canManageSurveyResponses" class="nav-item">
                  <RouterLink
                    :to="{ name: 'admin-survey-responses' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-clipboard-data"></i>
                    <p>Survey Responses</p>
                  </RouterLink>
                </li>
                <li v-if="canViewPriorityNeeds" class="nav-item">
                  <RouterLink
                    :to="{ name: 'admin-priority-needs' }"
                    class="nav-link text-white"
                    active-class="active"
                    @click="closeSidebarOnMobile"
                  >
                    <i class="nav-icon bi bi-bar-chart"></i>
                    <p>Priority Needs</p>
                  </RouterLink>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <main class="app-main">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useAuthorization } from "@/composables/useAuthorization";
import { ROLE_GROUPS } from "@/constants/roles";

const route = useRoute();
const authStore = useAuthStore();
const { canAccessRoles } = useAuthorization();

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
const windowWidth = ref(window.innerWidth);
const settingsOpen = ref(false);
const signingOut = ref(false);

const isMobile = computed(() => windowWidth.value < 992);

const canManageUsers = computed(() =>
  canAccessRoles(ROLE_GROUPS.USER_MANAGERS),
);
const canManageColleges = computed(() =>
  canAccessRoles(ROLE_GROUPS.COLLEGE_MANAGERS),
);
const canViewPrograms = computed(() =>
  canAccessRoles(ROLE_GROUPS.PROGRAM_VIEWERS),
);
const canViewProjects = computed(() =>
  canAccessRoles(ROLE_GROUPS.PROJECT_VIEWERS),
);
const canManageCommunities = computed(() =>
  canAccessRoles(ROLE_GROUPS.COMMUNITY_MANAGERS),
);
const canDesignSurveys = computed(() =>
  canAccessRoles(ROLE_GROUPS.SURVEY_DESIGNERS),
);
const canManageSurveyResponses = computed(() =>
  canAccessRoles(ROLE_GROUPS.SURVEY_RESPONDENTS),
);
const canViewPriorityNeeds = computed(() =>
  canAccessRoles(ROLE_GROUPS.PRIORITY_NEED_VIEWERS),
);

const hasSystemSettingsAccess = computed(() =>
  canManageUsers.value || canManageColleges.value,
);
const hasOutreachAccess = computed(() =>
  canViewPrograms.value || canViewProjects.value,
);
const hasCommunityAssessmentAccess = computed(() =>
  canManageCommunities.value ||
  canDesignSurveys.value ||
  canManageSurveyResponses.value ||
  canViewPriorityNeeds.value,
);
const hasSettingsAccess = computed(() =>
  hasSystemSettingsAccess.value ||
  hasOutreachAccess.value ||
  hasCommunityAssessmentAccess.value,
);

const currentUserName = computed(() => {
  const user = authStore.user;

  if (!user) {
    return "User";
  }

  return (
    user.full_name ||
    [user.first_name, user.middle_name, user.last_name]
      .filter(Boolean)
      .join(" ") ||
    user.name ||
    user.email ||
    "User"
  );
});

const settingsRouteNames = [
  "users",
  "colleges",
  "outreach-programs",
  "outreach-projects",
  "admin-communities",
  "admin-survey-templates",
  "admin-survey-questions",
  "admin-survey-responses",
  "admin-survey-responses-create",
  "admin-survey-responses-edit",
  "admin-survey-responses-view",
  "admin-priority-needs",
];

const isSettingsActive = computed(() =>
  settingsRouteNames.includes(route.name),
);

function updateSidebarClasses() {
  document.body.classList.toggle(
    "sidebar-open",
    isMobile.value && isSidebarOpen.value,
  );
  document.body.classList.toggle(
    "sidebar-collapse",
    !isMobile.value && isSidebarCollapsed.value,
  );
}

function toggleSidebar() {
  if (isMobile.value) {
    isSidebarOpen.value = !isSidebarOpen.value;
  } else {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
  }

  updateSidebarClasses();
}

function closeSidebar() {
  isSidebarOpen.value = false;
  updateSidebarClasses();
}

function closeSidebarOnMobile() {
  if (isMobile.value) {
    closeSidebar();
  }
}

function handleResize() {
  windowWidth.value = window.innerWidth;

  if (isMobile.value) {
    isSidebarCollapsed.value = false;
  } else {
    isSidebarOpen.value = false;
  }

  updateSidebarClasses();
}

async function signOut() {
  if (signingOut.value) {
    return;
  }

  signingOut.value = true;

  try {
    await authStore.logout();
  } finally {
    signingOut.value = false;
  }
}

watch(
  () => route.name,
  () => {
    if (isSettingsActive.value) {
      settingsOpen.value = true;
    }
  },
  { immediate: true },
);

onMounted(() => {
  window.addEventListener("resize", handleResize);
  updateSidebarClasses();
});

onUnmounted(() => {
  window.removeEventListener("resize", handleResize);
  document.body.classList.remove("sidebar-open", "sidebar-collapse");
});
</script>
