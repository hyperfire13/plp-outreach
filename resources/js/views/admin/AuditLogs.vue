<script setup>
import { onMounted, reactive, ref } from "vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import auditLogService from "@/services/auditLogService";
const logs = ref([]),
    pagination = ref({ current_page: 1, last_page: 1 }),
    loading = ref(false),
    failure = ref("");
const filters = reactive({
    search: "",
    module: "",
    action: "",
    page: 1,
    per_page: 20,
});
const name = (p) =>
    p?.full_name ||
    [p?.first_name, p?.middle_name, p?.last_name].filter(Boolean).join(" ") ||
    "System";
async function fetchLogs(page = 1) {
    loading.value = true;
    filters.page = page;
    try {
        const r = await auditLogService.paginate(filters);
        logs.value = r.data.data;
        pagination.value = r.data;
    } catch (e) {
        failure.value =
            e.response?.data?.message || "Unable to load audit logs.";
    } finally {
        loading.value = false;
    }
}
onMounted(() => fetchLogs());
</script>
<template>
    <MainLayout
        ><section class="content">
            <div class="container-fluid py-3">
                <h1 class="h3">Audit Trail</h1>
                <p class="text-muted">
                    Read-only history of changes, actors, request context, and
                    timestamps.
                </p>
                <div v-if="failure" class="alert alert-danger">
                    {{ failure }}
                </div>
                <div class="card mb-3">
                    <div class="card-body row g-2">
                        <div class="col-md-6">
                            <input
                                v-model.trim="filters.search"
                                class="form-control"
                                placeholder="Search description or actor email"
                            />
                        </div>
                        <div class="col-md-2">
                            <input
                                v-model.trim="filters.module"
                                class="form-control"
                                placeholder="Module"
                            />
                        </div>
                        <div class="col-md-2">
                            <input
                                v-model.trim="filters.action"
                                class="form-control"
                                placeholder="Action"
                            />
                        </div>
                        <div class="col-md-2">
                            <button
                                class="btn btn-primary w-100"
                                @click="fetchLogs(1)"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>Activity</th>
                                    <th>Doer</th>
                                    <th>Module</th>
                                    <th>IP / Request</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs" :key="log.id">
                                    <td class="text-nowrap">
                                        {{
                                            new Date(
                                                log.created_at,
                                            ).toLocaleString()
                                        }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-uppercase">
                                            {{ log.action }}
                                        </div>
                                        <small>{{ log.description }}</small>
                                    </td>
                                    <td>
                                        {{ name(log.actor) }}
                                        <div class="small text-muted">
                                            {{ log.actor?.email }}
                                        </div>
                                    </td>
                                    <td>{{ log.module }}</td>
                                    <td>
                                        <span>{{ log.ip_address || "—" }}</span>
                                        <div
                                            class="small text-muted text-break"
                                        >
                                            {{ log.request_id }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!loading && !logs.length">
                                    <td
                                        colspan="5"
                                        class="text-center text-muted py-5"
                                    >
                                        No audit entries found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <CrudPagination
                            :current-page="pagination.current_page"
                            :last-page="pagination.last_page"
                            :prev="!!pagination.prev_page_url"
                            :next="!!pagination.next_page_url"
                            @change="fetchLogs"
                        />
                    </div>
                </div>
            </div></section
    ></MainLayout>
</template>
