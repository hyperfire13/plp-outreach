<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import engagementRecordService from "@/services/engagementRecordService";
import { useAuthStore } from "@/stores/auth";
import { useAuthorization } from "@/composables/useAuthorization";
import { ROLE_GROUPS, ROLES } from "@/constants/roles";

const authStore = useAuthStore();
const { canAccessRoles, hasRole } = useAuthorization();
const modal = ref(null);
const records = ref([]);
const pagination = ref({ current_page: 1, last_page: 1 });
const options = ref({
    participants: [],
    projects: [],
    communities: [],
    engagement_types: [],
    source_types: [],
    statuses: [],
    sdgs: [],
});
const loading = ref(false);
const saving = ref(false);
const actionId = ref(null);
const editingId = ref(null);
const errors = ref({});
const notice = ref("");
const failure = ref("");
const participantSearch = ref("");

const filters = reactive({
    search: "",
    user_id: "",
    status: "",
    engagement_type: "",
    sdg: "",
    source_type: "",
    year: "",
    page: 1,
    per_page: 10,
});

const blankForm = () => ({
    user_id: authStore.userId || "",
    user_ids: [],
    outreach_project_id: "",
    community_id: "",
    title: "",
    engagement_type: "",
    participation_role: "",
    activity_date: "",
    service_hours: "",
    sdg: "",
    description: "",
    source_type: "manual",
    status: "draft",
});
const form = reactive(blankForm());

const canEncode = computed(() =>
    canAccessRoles(ROLE_GROUPS.ENGAGEMENT_ENCODERS),
);
const canReview = computed(() =>
    canAccessRoles(ROLE_GROUPS.ENGAGEMENT_REVIEWERS),
);
const modalTitle = computed(() =>
    editingId.value ? "Edit Engagement Record" : "Add Engagement Record",
);
const filteredParticipants = computed(() => {
    const search = participantSearch.value.trim().toLowerCase();

    if (!search) {
        return options.value.participants;
    }

    return options.value.participants.filter((person) =>
        [personName(person), person.email, person.college?.name]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(search)),
    );
});

const label = (value = "") =>
    value
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
const personName = (person) =>
    person?.full_name ||
    [person?.first_name, person?.middle_name, person?.last_name]
        .filter(Boolean)
        .join(" ") ||
    "Unknown user";
const statusClass = (status) =>
    ({
        draft: "text-bg-secondary",
        submitted: "text-bg-warning",
        approved: "text-bg-success",
        rejected: "text-bg-danger",
    })[status] || "text-bg-secondary";
const firstError = (key) => {
    const error = errors.value[key];
    return Array.isArray(error) ? error[0] : error;
};
const errorMessage = (error, fallback) =>
    error.response?.data?.message || error.message || fallback;

function clearMessages() {
    notice.value = "";
    failure.value = "";
}

async function loadOptions() {
    const response = await engagementRecordService.options();
    options.value = response.data;
}

async function fetchRecords(page = 1) {
    loading.value = true;
    failure.value = "";
    filters.page = page;
    try {
        const response = await engagementRecordService.paginate(filters);
        records.value = response.data.data;
        pagination.value = response.data;
    } catch (error) {
        failure.value = errorMessage(
            error,
            "Unable to load engagement records.",
        );
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    Object.assign(form, blankForm());
    editingId.value = null;
    errors.value = {};
    participantSearch.value = "";
}

function openCreate() {
    clearMessages();
    resetForm();
    modal.value?.open();
}

function openEdit(record) {
    clearMessages();
    editingId.value = record.id;
    errors.value = {};
    Object.assign(form, {
        user_id: record.user_id,
        user_ids: (record.participants || [record.user])
            .filter(Boolean)
            .map((participant) => participant.id),
        outreach_project_id: record.outreach_project_id || "",
        community_id: record.community_id || "",
        title: record.title,
        engagement_type: record.engagement_type,
        participation_role: record.participation_role,
        activity_date: record.activity_date,
        service_hours: record.service_hours ?? "",
        sdg: record.sdg || "",
        description: record.description || "",
        source_type: record.source_type,
        status: record.status,
    });
    modal.value?.open();
}

function normalizedPayload() {
    const payload = {
        ...form,
        user_id: Number(form.user_id),
        outreach_project_id: form.outreach_project_id
            ? Number(form.outreach_project_id)
            : null,
        community_id: form.community_id ? Number(form.community_id) : null,
        service_hours:
            form.service_hours === "" ? null : Number(form.service_hours),
        sdg: form.sdg || null,
        description: form.description || null,
    };

    if (hasRole(ROLES.COLLEGE_ADMIN)) {
        payload.user_ids = form.user_ids.map(Number);
        delete payload.user_id;
    } else {
        delete payload.user_ids;
    }

    return payload;
}

async function saveRecord() {
    saving.value = true;
    errors.value = {};
    failure.value = "";
    try {
        const payload = normalizedPayload();
        let response;
        if (editingId.value) {
            delete payload.status;
            response = await engagementRecordService.update(
                editingId.value,
                payload,
            );
        } else {
            response = await engagementRecordService.store(payload);
        }
        modal.value?.close();
        notice.value = response.message;
        await fetchRecords(editingId.value ? pagination.value.current_page : 1);
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
        failure.value = errorMessage(
            error,
            "Unable to save the engagement record.",
        );
    } finally {
        saving.value = false;
    }
}

function canManage(record) {
    if (!["draft", "rejected"].includes(record.status) || !canEncode.value)
        return false;
    if (hasRole(ROLES.SUPER_ADMIN, ROLES.CALO_ADMINISTRATOR)) return true;
    if (record.encoded_by === authStore.userId) return true;
    return (
        hasRole(ROLES.COLLEGE_ADMIN, ROLES.FACULTY_EXTENSION_COORDINATOR) &&
        Number(record.user?.college_id) === Number(authStore.collegeId)
    );
}

async function runAction(
    record,
    action,
    confirmation,
    successFallback,
    payload,
) {
    if (!window.confirm(confirmation)) return;
    actionId.value = record.id;
    clearMessages();
    try {
        const response = await engagementRecordService[action](
            record.id,
            payload,
        );
        notice.value = response.message || successFallback;
        await fetchRecords(pagination.value.current_page);
    } catch (error) {
        failure.value = errorMessage(
            error,
            `Unable to ${action} the engagement record.`,
        );
    } finally {
        actionId.value = null;
    }
}

function submitRecord(record) {
    return runAction(
        record,
        "submit",
        `Submit “${record.title}” for validation?`,
        "Record submitted.",
    );
}
function approveRecord(record) {
    return runAction(
        record,
        "approve",
        `Approve “${record.title}”?`,
        "Record approved.",
    );
}
async function rejectRecord(record) {
    const remarks = window.prompt("Reason for rejection:");
    if (remarks === null) return;
    if (!remarks.trim()) {
        failure.value = "A rejection reason is required.";
        return;
    }
    actionId.value = record.id;
    clearMessages();
    try {
        const response = await engagementRecordService.reject(
            record.id,
            remarks.trim(),
        );
        notice.value = response.message;
        await fetchRecords(pagination.value.current_page);
    } catch (error) {
        failure.value = errorMessage(
            error,
            "Unable to reject the engagement record.",
        );
    } finally {
        actionId.value = null;
    }
}
function deleteRecord(record) {
    return runAction(
        record,
        "remove",
        `Delete “${record.title}”? This cannot be undone.`,
        "Record deleted.",
    );
}

onMounted(async () => {
    try {
        await Promise.all([loadOptions(), fetchRecords()]);
    } catch (error) {
        failure.value = errorMessage(
            error,
            "Unable to initialize engagement records.",
        );
    }
});
</script>

<template>
    <MainLayout>
        <section class="content">
            <div class="container-fluid py-3">
                <div
                    class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"
                >
                    <div>
                        <h1 class="h3 mb-1">Engagement Records</h1>
                        <p class="text-muted mb-0">
                            Encode, validate, and monitor individual community
                            engagements.
                        </p>
                    </div>
                    <button
                        v-if="canEncode"
                        class="btn btn-primary"
                        @click="openCreate"
                    >
                        <i class="bi bi-plus-lg me-1"></i>Add Record
                    </button>
                </div>

                <div
                    v-if="notice"
                    class="alert alert-success alert-dismissible"
                >
                    {{ notice
                    }}<button class="btn-close" @click="notice = ''"></button>
                </div>
                <div
                    v-if="failure"
                    class="alert alert-danger alert-dismissible"
                >
                    {{ failure
                    }}<button class="btn-close" @click="failure = ''"></button>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-lg-3">
                                <input
                                    v-model.trim="filters.search"
                                    class="form-control"
                                    placeholder="Search title or role"
                                />
                            </div>
                            <div class="col-lg-2">
                                <select
                                    v-model="filters.user_id"
                                    class="form-select"
                                >
                                    <option value="">All participants</option>
                                    <option
                                        v-for="person in options.participants"
                                        :key="person.id"
                                        :value="person.id"
                                    >
                                        {{ personName(person) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select
                                    v-model="filters.status"
                                    class="form-select"
                                >
                                    <option value="">All statuses</option>
                                    <option
                                        v-for="status in options.statuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ label(status) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select
                                    v-model="filters.engagement_type"
                                    class="form-select"
                                >
                                    <option value="">All types</option>
                                    <option
                                        v-for="type in options.engagement_types"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ label(type) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-1">
                                <input
                                    v-model="filters.year"
                                    type="number"
                                    min="1900"
                                    max="2100"
                                    class="form-control"
                                    placeholder="Year"
                                />
                            </div>
                            <div class="col-lg-2">
                                <button
                                    class="btn btn-primary w-100"
                                    @click="fetchRecords(1)"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div v-if="loading" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                        </div>
                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Participants</th>
                                        <th>Date / Hours</th>
                                        <th>Type / SDG</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="record in records"
                                        :key="record.id"
                                    >
                                        <td>
                                            <div class="fw-semibold">
                                                {{ record.title }}
                                            </div>
                                            <small class="text-muted">{{
                                                record.participation_role
                                            }}</small>
                                        </td>
                                        <td>
                                            <div
                                                v-for="participant in record.participants || [
                                                    record.user,
                                                ]"
                                                :key="participant.id"
                                                class="mb-1"
                                            >
                                                <RouterLink
                                                    :to="{
                                                        name: 'engagement-profile-view',
                                                        params: {
                                                            userId: participant.id,
                                                        },
                                                    }"
                                                    >{{
                                                        personName(participant)
                                                    }}</RouterLink
                                                >
                                                <span class="small text-muted">
                                                    —
                                                    {{
                                                        participant.college
                                                            ?.name ||
                                                        "No college"
                                                    }}
                                                </span>
                                            </div>
                                            <span class="badge text-bg-light">
                                                {{
                                                    record.participant_count ||
                                                    1
                                                }}
                                                participant{{
                                                    (record.participant_count ||
                                                        1) === 1
                                                        ? ""
                                                        : "s"
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ record.activity_date }}
                                            <div class="small text-muted">
                                                {{ record.service_hours ?? 0 }}
                                                service hours
                                            </div>
                                        </td>
                                        <td>
                                            {{ label(record.engagement_type) }}
                                            <div class="small text-muted">
                                                {{
                                                    record.sdg
                                                        ? label(record.sdg)
                                                        : "No SDG"
                                                }}
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge"
                                                :class="
                                                    statusClass(record.status)
                                                "
                                                >{{
                                                    label(record.status)
                                                }}</span
                                            >
                                            <div
                                                v-if="record.validation_remarks"
                                                class="small text-danger mt-1"
                                                :title="
                                                    record.validation_remarks
                                                "
                                            >
                                                Review remarks available
                                            </div>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <button
                                                v-if="canManage(record)"
                                                class="btn btn-sm btn-outline-primary me-1"
                                                title="Edit"
                                                :disabled="
                                                    actionId === record.id
                                                "
                                                @click="openEdit(record)"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button
                                                v-if="canManage(record)"
                                                class="btn btn-sm btn-outline-info me-1"
                                                title="Submit"
                                                :disabled="
                                                    actionId === record.id
                                                "
                                                @click="submitRecord(record)"
                                            >
                                                <i class="bi bi-send"></i>
                                            </button>
                                            <button
                                                v-if="
                                                    canReview &&
                                                    record.status ===
                                                        'submitted'
                                                "
                                                class="btn btn-sm btn-outline-success me-1"
                                                title="Approve"
                                                :disabled="
                                                    actionId === record.id
                                                "
                                                @click="approveRecord(record)"
                                            >
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button
                                                v-if="
                                                    canReview &&
                                                    record.status ===
                                                        'submitted'
                                                "
                                                class="btn btn-sm btn-outline-warning me-1"
                                                title="Reject"
                                                :disabled="
                                                    actionId === record.id
                                                "
                                                @click="rejectRecord(record)"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <button
                                                v-if="canManage(record)"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                :disabled="
                                                    actionId === record.id
                                                "
                                                @click="deleteRecord(record)"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="records.length === 0">
                                        <td
                                            colspan="6"
                                            class="text-center text-muted py-5"
                                        >
                                            No engagement records found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <CrudPagination
                            :current-page="pagination.current_page"
                            :last-page="pagination.last_page"
                            :prev="Boolean(pagination.prev_page_url)"
                            :next="Boolean(pagination.next_page_url)"
                            @change="fetchRecords"
                        />
                    </div>
                </div>
            </div>
        </section>

        <CrudModal
            ref="modal"
            :title="modalTitle"
            :form="form"
            :errors="errors"
            size="xl"
            @hidden="resetForm"
        >
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"
                        >{{
                            hasRole(ROLES.COLLEGE_ADMIN)
                                ? "Participants"
                                : "Participant"
                        }}
                        <span class="text-danger">*</span></label
                    >
                    <div
                        v-if="hasRole(ROLES.COLLEGE_ADMIN)"
                        class="border rounded p-2"
                    >
                        <input
                            v-model.trim="participantSearch"
                            type="search"
                            class="form-control form-control-sm mb-2"
                            placeholder="Search participants..."
                        />
                        <div class="overflow-auto" style="max-height: 240px">
                            <div
                                v-for="person in filteredParticipants"
                                :key="person.id"
                                class="form-check py-1"
                            >
                                <input
                                    :id="`participant-${person.id}`"
                                    v-model="form.user_ids"
                                    class="form-check-input"
                                    type="checkbox"
                                    :value="person.id"
                                />
                                <label
                                    class="form-check-label w-100"
                                    :for="`participant-${person.id}`"
                                >
                                    <span class="fw-semibold">{{
                                        personName(person)
                                    }}</span>
                                    <span class="d-block small text-muted">
                                        {{ person.email
                                        }}{{
                                            person.college?.name
                                                ? ` — ${person.college.name}`
                                                : ""
                                        }}
                                    </span>
                                </label>
                            </div>
                            <div
                                v-if="filteredParticipants.length === 0"
                                class="small text-muted text-center py-3"
                            >
                                No participants match your search.
                            </div>
                        </div>
                    </div>
                    <select v-else v-model="form.user_id" class="form-select">
                        <option value="">Select participant</option>
                        <option
                            v-for="person in options.participants"
                            :key="person.id"
                            :value="person.id"
                        >
                            {{ personName(person)
                            }}{{
                                person.college?.name
                                    ? ` — ${person.college.name}`
                                    : ""
                            }}
                        </option>
                    </select>
                    <div v-if="hasRole(ROLES.COLLEGE_ADMIN)" class="form-text">
                        {{ form.user_ids.length }} participant{{
                            form.user_ids.length === 1 ? "" : "s"
                        }}
                        selected.
                    </div>
                    <small
                        v-if="firstError('user_id') || firstError('user_ids')"
                        class="text-danger"
                        >{{
                            firstError("user_id") || firstError("user_ids")
                        }}</small
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label"
                        >Activity Title
                        <span class="text-danger">*</span></label
                    ><input
                        v-model.trim="form.title"
                        class="form-control"
                    /><small v-if="firstError('title')" class="text-danger">{{
                        firstError("title")
                    }}</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label"
                        >Engagement Type
                        <span class="text-danger">*</span></label
                    ><select v-model="form.engagement_type" class="form-select">
                        <option value="">Select type</option>
                        <option
                            v-for="type in options.engagement_types"
                            :key="type"
                            :value="type"
                        >
                            {{ label(type) }}
                        </option></select
                    ><small
                        v-if="firstError('engagement_type')"
                        class="text-danger"
                        >{{ firstError("engagement_type") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label"
                        >Participation Role
                        <span class="text-danger">*</span></label
                    ><input
                        v-model.trim="form.participation_role"
                        class="form-control"
                        placeholder="Volunteer, facilitator, organizer..."
                    /><small
                        v-if="firstError('participation_role')"
                        class="text-danger"
                        >{{ firstError("participation_role") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label"
                        >Activity Date <span class="text-danger">*</span></label
                    ><input
                        v-model="form.activity_date"
                        type="date"
                        class="form-control"
                    /><small
                        v-if="firstError('activity_date')"
                        class="text-danger"
                        >{{ firstError("activity_date") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label"
                        >Source <span class="text-danger">*</span></label
                    ><select v-model="form.source_type" class="form-select">
                        <option
                            v-for="source in options.source_types"
                            :key="source"
                            :value="source"
                        >
                            {{ label(source) }}
                        </option></select
                    ><small
                        v-if="firstError('source_type')"
                        class="text-danger"
                        >{{ firstError("source_type") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Outreach Project</label
                    ><select
                        v-model="form.outreach_project_id"
                        class="form-select"
                    >
                        <option value="">No linked project</option>
                        <option
                            v-for="project in options.projects"
                            :key="project.id"
                            :value="project.id"
                        >
                            {{ project.title }}
                        </option></select
                    ><small
                        v-if="firstError('outreach_project_id')"
                        class="text-danger"
                        >{{ firstError("outreach_project_id") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Community</label
                    ><select v-model="form.community_id" class="form-select">
                        <option value="">No linked community</option>
                        <option
                            v-for="community in options.communities"
                            :key="community.id"
                            :value="community.id"
                        >
                            {{ community.name }}
                        </option></select
                    ><small
                        v-if="firstError('community_id')"
                        class="text-danger"
                        >{{ firstError("community_id") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service Hours</label
                    ><input
                        v-model="form.service_hours"
                        type="number"
                        min="0"
                        max="10000"
                        step="0.25"
                        class="form-control"
                    /><small
                        v-if="firstError('service_hours')"
                        class="text-danger"
                        >{{ firstError("service_hours") }}</small
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label">SDG Alignment</label
                    ><select v-model="form.sdg" class="form-select">
                        <option value="">No SDG selected</option>
                        <option
                            v-for="sdg in options.sdgs"
                            :key="sdg"
                            :value="sdg"
                        >
                            {{ label(sdg) }}
                        </option></select
                    ><small v-if="firstError('sdg')" class="text-danger">{{
                        firstError("sdg")
                    }}</small>
                </div>
                <div v-if="!editingId" class="col-md-4">
                    <label class="form-label">Initial Status</label
                    ><select v-model="form.status" class="form-select">
                        <option value="draft">Save as Draft</option>
                        <option value="submitted">
                            Submit for Validation
                        </option></select
                    ><small v-if="firstError('status')" class="text-danger">{{
                        firstError("status")
                    }}</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label
                    ><textarea
                        v-model.trim="form.description"
                        rows="3"
                        class="form-control"
                    ></textarea
                    ><small
                        v-if="firstError('description')"
                        class="text-danger"
                        >{{ firstError("description") }}</small
                    >
                </div>
            </div>
            <template #footer
                ><button
                    class="btn btn-secondary"
                    type="button"
                    :disabled="saving"
                    @click="modal?.close()"
                >
                    Cancel</button
                ><button
                    class="btn btn-primary"
                    type="button"
                    :disabled="saving"
                    @click="saveRecord"
                >
                    <span
                        v-if="saving"
                        class="spinner-border spinner-border-sm me-1"
                    ></span
                    >{{ saving ? "Saving..." : "Save Record" }}
                </button></template
            >
        </CrudModal>
    </MainLayout>
</template>
