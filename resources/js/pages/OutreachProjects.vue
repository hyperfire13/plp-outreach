<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import MainLayout from "@/components/layouts/MainLayout.vue";
import CrudPage from "@/components/crud/CrudPage.vue";
import CrudTable from "@/components/crud/CrudTable.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import OutreachProjectFormModal from "@/components/outreach-projects/OutreachProjectFormModal.vue";
import ProjectStatusBadge from "@/components/outreach-projects/ProjectStatusBadge.vue";

import outreachProjectService from "@/services/outreachProjectService";
import outreachProgramService from "@/services/outreachProgramService";
import collegeService from "@/services/collegeService";
import userService from "@/services/userService";

import { useAuthStore } from "@/stores/auth";
import { useApiErrors } from "@/composables/useApiErrors";
import { useAuthorization } from "@/composables/useAuthorization";
import { ROLES } from "@/constants/roles";

const authStore = useAuthStore();

const {
    errors,
    generalError,
    clearErrors,
    captureError,
} = useApiErrors();

const {
    hasRole,
    isAdministrator,
    canCreateProject,
} = useAuthorization();

const modalRef = ref(null);

const projects = ref([]);
const programs = ref([]);
const colleges = ref([]);
const coordinators = ref([]);

const loading = ref(false);
const submitting = ref(false);
const deletingId = ref(null);
const editingId = ref(null);
const search = ref("");
const statusFilter = ref("");

const pagination = reactive({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
});

const emptyForm = () => ({
    outreach_program_id: "",
    college_id: authStore.user?.college_id ?? "",
    coordinator_id: "",
    title: "",
    description: "",
    objectives: "",
    location: "",
    start_date: "",
    end_date: "",
    proposed_budget: "",
    expected_beneficiaries: "",
});

const form = ref(emptyForm());

const columns = [
    {
        key: "title",
        label: "Project",
    },
    {
        key: "program_name",
        label: "Program",
    },
    {
        key: "college_name",
        label: "College",
    },
    {
        key: "schedule",
        label: "Schedule",
    },
    {
        key: "proposed_budget_display",
        label: "Budget",
    },
    {
        key: "status",
        label: "Status",
    },
];

const canAssignCollege = computed(
    () =>
        isAdministrator.value ||
        hasRole(ROLES.COLLEGE_ADMIN),
);

const canAssignCoordinator = computed(
    () =>
        isAdministrator.value ||
        hasRole(
            ROLES.COLLEGE_ADMIN,
            ROLES.FACULTY_EXTENSION_COORDINATOR,
        ),
);

const formattedProjects = computed(() =>
    projects.value.map((project) => ({
        ...project,

        program_name:
            project.program?.name ?? "Not specified",

        college_name:
            project.college?.name ?? "Not specified",

        schedule: formatSchedule(
            project.start_date,
            project.end_date,
        ),

        proposed_budget_display:
            formatCurrency(project.proposed_budget),
    })),
);

const formatCurrency = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(value));
};

const formatDate = (value) => {
    if (!value) {
        return "";
    }

    return new Intl.DateTimeFormat("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    }).format(new Date(`${value}T00:00:00`));
};

const formatSchedule = (startDate, endDate) => {
    if (!startDate && !endDate) {
        return "Not scheduled";
    }

    if (startDate && !endDate) {
        return formatDate(startDate);
    }

    return `${formatDate(startDate)} – ${formatDate(endDate)}`;
};

const fetchProjects = async (page = 1) => {
    loading.value = true;

    try {
        const response =
            await outreachProjectService.paginate({
                page,
                per_page: pagination.perPage,
                search: search.value || undefined,
                status: statusFilter.value || undefined,
            });

        projects.value = response.data ?? [];

        pagination.currentPage =
            response.current_page ?? 1;

        pagination.lastPage =
            response.last_page ?? 1;

        pagination.total = response.total ?? 0;
    } catch (error) {
        captureError(error);
    } finally {
        loading.value = false;
    }
};

const fetchDependencies = async () => {
    const requests = [
        outreachProgramService.all(),
        collegeService.all(),
    ];

    if (canAssignCoordinator.value) {
        requests.push(
            userService.all({
                role: ROLES.FACULTY_EXTENSION_COORDINATOR,
                college_id:
                    form.value.college_id || undefined,
            }),
        );
    }

    const results = await Promise.allSettled(requests);

    programs.value =
        results[0].status === "fulfilled"
            ? results[0].value.data ?? results[0].value
            : [];

    colleges.value =
        results[1].status === "fulfilled"
            ? results[1].value.data ?? results[1].value
            : [];

    if (results[2]?.status === "fulfilled") {
        coordinators.value =
            results[2].value.data ?? results[2].value;
    }
};

const openCreate = () => {
    editingId.value = null;
    form.value = emptyForm();
    clearErrors();
    modalRef.value?.open();
};

const openEdit = (project) => {
    editingId.value = project.id;
    clearErrors();

    form.value = {
        outreach_program_id:
            project.outreach_program_id ?? "",
        college_id: project.college_id ?? "",
        coordinator_id: project.coordinator_id ?? "",
        title: project.title ?? "",
        description: project.description ?? "",
        objectives: project.objectives ?? "",
        location: project.location ?? "",
        start_date: project.start_date ?? "",
        end_date: project.end_date ?? "",
        proposed_budget:
            project.proposed_budget ?? "",
        expected_beneficiaries:
            project.expected_beneficiaries ?? "",
    };

    modalRef.value?.open();
};

const normalizePayload = () => ({
    outreach_program_id: Number(
        form.value.outreach_program_id,
    ),

    college_id: Number(form.value.college_id),

    coordinator_id: form.value.coordinator_id
        ? Number(form.value.coordinator_id)
        : null,

    title: form.value.title,
    description: form.value.description || null,
    objectives: form.value.objectives || null,
    location: form.value.location || null,
    start_date: form.value.start_date || null,
    end_date: form.value.end_date || null,

    proposed_budget:
        form.value.proposed_budget === ""
            ? null
            : Number(form.value.proposed_budget),

    expected_beneficiaries:
        form.value.expected_beneficiaries === ""
            ? null
            : Number(
                  form.value.expected_beneficiaries,
              ),
});

const submit = async () => {
    submitting.value = true;
    clearErrors();

    try {
        const payload = normalizePayload();

        if (editingId.value) {
            await outreachProjectService.update(
                editingId.value,
                payload,
            );
        } else {
            await outreachProjectService.store(payload);
        }

        modalRef.value?.close();

        await fetchProjects(
            pagination.currentPage,
        );
    } catch (error) {
        captureError(error);
    } finally {
        submitting.value = false;
    }
};

const remove = async (project) => {
    const confirmed = window.confirm(
        `Delete "${project.title}"?`,
    );

    if (!confirmed) {
        return;
    }

    deletingId.value = project.id;

    try {
        await outreachProjectService.remove(project.id);

        const nextPage =
            projects.value.length === 1 &&
            pagination.currentPage > 1
                ? pagination.currentPage - 1
                : pagination.currentPage;

        await fetchProjects(nextPage);
    } catch (error) {
        captureError(error);
    } finally {
        deletingId.value = null;
    }
};

const canEditProject = (project) => {
    if (isAdministrator.value) {
        return true;
    }

    return (
        project.created_by === authStore.user?.id &&
        ["draft", "returned"].includes(project.status)
    );
};

const canDeleteProject = (project) => {
    if (isAdministrator.value) {
        return true;
    }

    return (
        project.created_by === authStore.user?.id &&
        project.status === "draft"
    );
};

const handleSearch = () => {
    fetchProjects(1);
};

const resetForm = () => {
    form.value = emptyForm();
    editingId.value = null;
    clearErrors();
};

onMounted(async () => {
    await Promise.all([
        fetchDependencies(),
        fetchProjects(),
    ]);
});
</script>

<template>
    <MainLayout>
        <CrudPage
            title="Outreach Projects"
            subtitle="Manage actual outreach project implementations."
            :loading="loading"
            :can-create="canCreateProject"
            create-label="Create Project"
            @create="openCreate"
        >
            <template #filters>
                <div class="row g-2">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input
                                v-model.trim="search"
                                type="search"
                                class="form-control"
                                placeholder="Search project title..."
                                @keyup.enter="handleSearch"
                            />

                            <button
                                class="btn btn-outline-primary"
                                type="button"
                                @click="handleSearch"
                            >
                                Search
                            </button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <select
                            v-model="statusFilter"
                            class="form-select"
                            @change="fetchProjects(1)"
                        >
                            <option value="">
                                All statuses
                            </option>
                            <option value="draft">
                                Draft
                            </option>
                            <option value="submitted">
                                Submitted
                            </option>
                            <option value="under_review">
                                Under Review
                            </option>
                            <option value="approved">
                                Approved
                            </option>
                            <option value="ongoing">
                                Ongoing
                            </option>
                            <option value="completed">
                                Completed
                            </option>
                            <option value="rejected">
                                Rejected
                            </option>
                        </select>
                    </div>
                </div>
            </template>

            <div
                v-if="generalError"
                class="alert alert-danger"
            >
                {{ generalError }}
            </div>

            <CrudTable
                :columns="columns"
                :rows="formattedProjects"
                :loading="loading"
                empty-message="No outreach projects found."
            >
                <template #cell-status="{ row }">
                    <ProjectStatusBadge
                        :status="row.status"
                    />
                </template>

                <template #actions="{ row }">
                    <button
                        v-if="canEditProject(row)"
                        type="button"
                        class="btn btn-sm btn-outline-primary me-1"
                        @click="openEdit(row)"
                    >
                        Edit
                    </button>

                    <button
                        v-if="canDeleteProject(row)"
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        :disabled="deletingId === row.id"
                        @click="remove(row)"
                    >
                        <span
                            v-if="deletingId === row.id"
                            class="spinner-border spinner-border-sm"
                        />

                        <span v-else>Delete</span>
                    </button>
                </template>
            </CrudTable>

            <CrudPagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :total="pagination.total"
                @change="fetchProjects"
            />
        </CrudPage>

        <OutreachProjectFormModal
            ref="modalRef"
            v-model="form"
            :programs="programs"
            :colleges="colleges"
            :coordinators="coordinators"
            :errors="errors"
            :general-error="generalError"
            :submitting="submitting"
            :editing="Boolean(editingId)"
            :can-assign-college="canAssignCollege"
            :can-assign-coordinator="canAssignCoordinator"
            @submit="submit"
            @closed="resetForm"
        />
    </MainLayout>
</template>
