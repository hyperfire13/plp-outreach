<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import MainLayout from "@/components/layout/MainLayout.vue";
import dashboardService from "@/services/dashboardService";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const dashboard = ref({ cards: [], project_statuses: {}, pending_engagement_reviews: 0, recent_projects: [], recent_engagements: [] });
const loading = ref(false);
const failure = ref("");
const name = computed(() => authStore.user?.first_name || authStore.user?.full_name || "User");
const statusTotal = computed(() => Object.values(dashboard.value.project_statuses || {}).reduce((sum, count) => sum + Number(count), 0));
const label = (value = "") => value.replaceAll("_", " ").replace(/\b\w/g, character => character.toUpperCase());
const statusClass = status => ({ draft: "secondary", submitted: "warning", approved: "primary", ongoing: "info", completed: "success", returned: "danger", rejected: "danger" }[status] || "secondary");
const formatValue = card => card.key === "service_hours" ? Number(card.value).toLocaleString(undefined, { maximumFractionDigits: 2 }) : Number(card.value).toLocaleString();

async function fetchDashboard() {
    loading.value = true; failure.value = "";
    try { dashboard.value = (await dashboardService.get()).data; }
    catch (error) { failure.value = error.response?.data?.message || "Unable to load the dashboard."; }
    finally { loading.value = false; }
}

onMounted(fetchDashboard);
</script>

<template>
  <MainLayout><section class="content"><div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div><h1 class="h3 mb-1">Welcome, {{ name }}</h1><p class="text-muted mb-0">Here is your current community outreach overview.</p></div>
      <button class="btn btn-outline-success" :disabled="loading" @click="fetchDashboard"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
    </div>
    <div v-if="failure" class="alert alert-danger">{{ failure }}</div>
    <div v-if="dashboard.pending_engagement_reviews" class="alert alert-warning d-flex justify-content-between align-items-center">
      <span><strong>{{ dashboard.pending_engagement_reviews }}</strong> engagement record(s) await validation.</span>
      <RouterLink :to="{ name: 'admin-engagement-records', query: { status: 'submitted' } }" class="btn btn-sm btn-warning">Review records</RouterLink>
    </div>
    <div class="row g-3 mb-3">
      <div v-for="card in dashboard.cards" :key="card.key" class="col-sm-6 col-xl-3">
        <RouterLink :to="{ name: card.route }" class="card h-100 text-decoration-none text-reset shadow-sm">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div><div class="text-muted small">{{ card.label }}</div><div class="h2 mb-0 fw-semibold">{{ formatValue(card) }}</div></div>
            <span class="rounded-circle p-3" :class="`text-bg-${card.color}`"><i class="bi fs-4" :class="card.icon"></i></span>
          </div>
        </RouterLink>
      </div>
      <div v-if="loading && !dashboard.cards.length" class="col-12 text-center py-5"><span class="spinner-border text-success"></span></div>
    </div>
    <div class="row g-3">
      <div class="col-lg-5"><div class="card h-100 shadow-sm"><div class="card-header"><h2 class="card-title mb-0">Project Status</h2></div><div class="card-body">
        <div v-if="!statusTotal" class="text-muted text-center py-4">No accessible projects found.</div>
        <div v-for="(count, status) in dashboard.project_statuses" :key="status" class="mb-3">
          <div class="d-flex justify-content-between mb-1"><span>{{ label(status) }}</span><strong>{{ count }}</strong></div>
          <div class="progress" style="height: 8px"><div class="progress-bar" :class="`bg-${statusClass(status)}`" :style="{ width: `${statusTotal ? (Number(count) / statusTotal) * 100 : 0}%` }"></div></div>
        </div>
      </div></div></div>
      <div class="col-lg-7"><div class="card h-100 shadow-sm"><div class="card-header d-flex justify-content-between"><h2 class="card-title mb-0">Recent Projects</h2><RouterLink :to="{ name: 'outreach-projects' }" class="small">View all</RouterLink></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Project</th><th>College</th><th>Status</th></tr></thead><tbody>
        <tr v-for="project in dashboard.recent_projects" :key="project.id"><td><div class="fw-semibold">{{ project.title }}</div><small class="text-muted">{{ project.program?.name || "No program" }}</small></td><td>{{ project.college?.name || "-" }}</td><td><span class="badge" :class="`text-bg-${statusClass(project.status)}`">{{ label(project.status) }}</span></td></tr>
        <tr v-if="!dashboard.recent_projects.length"><td colspan="3" class="text-center text-muted py-4">No recent projects.</td></tr>
      </tbody></table></div></div></div></div>
      <div class="col-12"><div class="card shadow-sm"><div class="card-header d-flex justify-content-between"><h2 class="card-title mb-0">Recent Engagement Activity</h2><RouterLink :to="{ name: 'admin-engagement-records' }" class="small">View all</RouterLink></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Activity</th><th>Participant</th><th>Date</th><th>Status</th></tr></thead><tbody>
        <tr v-for="record in dashboard.recent_engagements" :key="record.id"><td class="fw-semibold">{{ record.title }}</td><td>{{ record.user?.full_name || [record.user?.first_name, record.user?.middle_name, record.user?.last_name].filter(Boolean).join(" ") || "-" }}</td><td>{{ record.activity_date }}</td><td><span class="badge" :class="`text-bg-${statusClass(record.status)}`">{{ label(record.status) }}</span></td></tr>
        <tr v-if="!dashboard.recent_engagements.length"><td colspan="4" class="text-center text-muted py-4">No recent engagement activity.</td></tr>
      </tbody></table></div></div></div></div>
    </div>
  </div></section></MainLayout>
</template>
