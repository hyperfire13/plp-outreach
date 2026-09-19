<script setup>
import { onMounted, reactive, ref } from "vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import auditLogService from "@/services/auditLogService";

const detailsModal = ref(null);
const logs = ref([]);
const selectedLog = ref(null);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const options = ref({ modules: [], actions: [] });
const loading = ref(false);
const failure = ref("");

const filters = reactive({
    search: "",
    module: "",
    action: "",
    date_from: "",
    date_to: "",
    page: 1,
    per_page: 20,
});

function formatLabel(value = "") {
    return value
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function actorName(actor) {
    return (
        actor?.full_name ||
        [actor?.first_name, actor?.middle_name, actor?.last_name]
            .filter(Boolean)
            .join(" ") ||
        "System"
    );
}

function formatDateTime(value) {
    if (!value) {
        return "—";
    }

    return new Intl.DateTimeFormat("en-PH", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

function actionBadgeClass(action) {
    return (
        {
            post: "text-bg-success",
            put: "text-bg-primary",
            patch: "text-bg-primary",
            delete: "text-bg-danger",
        }[action] || "text-bg-secondary"
    );
}

function formattedJson(value) {
    if (!value || Object.keys(value).length === 0) {
        return "No values recorded.";
    }

    return JSON.stringify(value, null, 2);
}

async function fetchLogs(page = 1) {
    loading.value = true;
    failure.value = "";
    filters.page = page;

    try {
        const response = await auditLogService.paginate(filters);

        logs.value = response.data.data;
        pagination.value = response.data;
        options.value = response.meta;
    } catch (error) {
        failure.value =
            error.response?.data?.message || "Unable to load audit logs.";
    } finally {
        loading.value = false;
    }
}

function resetFilters() {
    Object.assign(filters, {
        search: "",
        module: "",
        action: "",
        date_from: "",
        date_to: "",
        page: 1,
        per_page: 20,
    });

    fetchLogs(1);
}

function openDetails(log) {
    selectedLog.value = log;
    detailsModal.value?.open();
}

onMounted(() => fetchLogs());
</script>

<template>
    <MainLayout>
        <section class="content">
            <div class="container-fluid py-3">
                <div
                    class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"
                >
                    <div>
                        <h1 class="h3 mb-1">Audit Trail</h1>
                        <p class="text-muted mb-0">
                            Monitor protected system activities, responsible
                            users, changes, and request context.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        :disabled="loading"
                        @click="fetchLogs(pagination.current_page || 1)"
                    >
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Refresh
                    </button>
                </div>

                <div
                    v-if="failure"
                    class="alert alert-danger alert-dismissible"
                    role="alert"
                >
                    {{ failure }}
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        @click="failure = ''"
                    ></button>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Search</label>
                                <input
                                    v-model.trim="filters.search"
                                    class="form-control"
                                    placeholder="Activity, user, IP, request ID"
                                    @keyup.enter="fetchLogs(1)"
                                />
                            </div>

                            <div class="col-xl-2 col-md-3">
                                <label class="form-label">Module</label>
                                <select
                                    v-model="filters.module"
                                    class="form-select"
                                >
                                    <option value="">All modules</option>
                                    <option
                                        v-for="module in options.modules"
                                        :key="module"
                                        :value="module"
                                    >
                                        {{ formatLabel(module) }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-xl-2 col-md-3">
                                <label class="form-label">Action</label>
                                <select
                                    v-model="filters.action"
                                    class="form-select"
                                >
                                    <option value="">All actions</option>
                                    <option
                                        v-for="action in options.actions"
                                        :key="action"
                                        :value="action"
                                    >
                                        {{ formatLabel(action) }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-xl-2 col-md-3">
                                <label class="form-label">From</label>
                                <input
                                    v-model="filters.date_from"
                                    type="date"
                                    class="form-control"
                                />
                            </div>

                            <div class="col-xl-2 col-md-3">
                                <label class="form-label">To</label>
                                <input
                                    v-model="filters.date_to"
                                    type="date"
                                    class="form-control"
                                />
                            </div>

                            <div
                                class="col-xl-1 col-md-12 d-flex gap-2 align-items-end"
                            >
                                <button
                                    type="button"
                                    class="btn btn-primary flex-grow-1"
                                    :disabled="loading"
                                    title="Apply filters"
                                    @click="fetchLogs(1)"
                                >
                                    <i class="bi bi-funnel"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary flex-grow-1"
                                    :disabled="loading"
                                    title="Reset filters"
                                    @click="resetFilters"
                                >
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <span class="fw-semibold">Recorded Activities</span>
                        <span class="badge text-bg-secondary">
                            {{ pagination.total || 0 }} total
                        </span>
                    </div>

                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <div class="text-muted mt-2">
                            Loading audit trail...
                        </div>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date and Time</th>
                                    <th>Activity</th>
                                    <th>Performed By</th>
                                    <th>Module</th>
                                    <th>Request</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs" :key="log.id">
                                    <td class="text-nowrap">
                                        {{ formatDateTime(log.created_at) }}
                                    </td>
                                    <td>
                                        <span
                                            class="badge me-2"
                                            :class="
                                                actionBadgeClass(log.action)
                                            "
                                        >
                                            {{ log.action.toUpperCase() }}
                                        </span>
                                        <span>{{ log.description }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ actorName(log.actor) }}
                                        </div>
                                        <small class="text-muted">
                                            {{
                                                log.actor?.email ||
                                                "System activity"
                                            }}
                                        </small>
                                    </td>
                                    <td>{{ formatLabel(log.module) }}</td>
                                    <td>
                                        <div>{{ log.ip_address || "—" }}</div>
                                        <small class="text-muted">
                                            {{ log.http_method }}
                                            {{
                                                log.route_name ||
                                                "Unnamed route"
                                            }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            @click="openDetails(log)"
                                        >
                                            <i class="bi bi-eye me-1"></i>
                                            View
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="logs.length === 0">
                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-5"
                                    >
                                        No audit entries match the selected
                                        filters.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <CrudPagination
                            :current-page="pagination.current_page || 1"
                            :last-page="pagination.last_page || 1"
                            :prev="Boolean(pagination.prev_page_url)"
                            :next="Boolean(pagination.next_page_url)"
                            @change="fetchLogs"
                        />
                    </div>
                </div>
            </div>
        </section>

        <CrudModal
            ref="detailsModal"
            title="Audit Entry Details"
            :form="{}"
            :errors="{}"
            size="lg"
        >
            <div v-if="selectedLog" class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Performed By</div>
                    <div class="fw-semibold">
                        {{ actorName(selectedLog.actor) }}
                    </div>
                    <div class="small">
                        {{ selectedLog.actor?.email || "System" }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Date and Time</div>
                    <div>{{ formatDateTime(selectedLog.created_at) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Module / Action</div>
                    <div>
                        {{ formatLabel(selectedLog.module) }} /
                        {{ selectedLog.action.toUpperCase() }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Route / Method</div>
                    <div>
                        {{ selectedLog.http_method || "—" }}
                        {{ selectedLog.route_name || "Unnamed route" }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">IP Address</div>
                    <div>{{ selectedLog.ip_address || "—" }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Request ID</div>
                    <div class="text-break">
                        {{ selectedLog.request_id || "—" }}
                    </div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Activity</div>
                    <div>{{ selectedLog.description }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1">Previous Values</div>
                    <pre
                        class="bg-light border rounded p-2 mb-0 overflow-auto"
                        >{{ formattedJson(selectedLog.old_values) }}</pre>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1">Submitted Values</div>
                    <pre
                        class="bg-light border rounded p-2 mb-0 overflow-auto"
                        >{{ formattedJson(selectedLog.new_values) }}</pre>
                </div>
            </div>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="detailsModal?.close()"
                >
                    Close
                </button>
            </template>
        </CrudModal>
    </MainLayout>
</template>
