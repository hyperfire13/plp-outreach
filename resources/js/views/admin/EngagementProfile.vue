<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import engagementRecordService from "@/services/engagementRecordService";
import { downloadResponse } from "@/utils/downloadResponse";

const route = useRoute();
const profile = ref(null);
const loading = ref(false);
const downloading = ref(false);
const failure = ref("");
const filters = reactive({ year: "", engagement_type: "", sdg: "", page: 1, per_page: 10 });
const engagementTypes = ["outreach", "extension", "volunteerism", "service_learning", "training", "other"];
const sdgs = Array.from({ length: 17 }, (_, index) => `sdg_${index + 1}`);
const userId = computed(() => route.params.userId || null);
const personName = computed(() => {
    const user = profile.value?.user;
    return user?.full_name || [user?.first_name, user?.middle_name, user?.last_name]
        .filter(Boolean).join(" ") || "Engagement Profile";
});
const label = (value = "") => value.replaceAll("_", " ")
    .replace(/\b\w/g, (character) => character.toUpperCase());

async function fetchProfile(page = 1) {
    loading.value = true;
    failure.value = "";
    filters.page = page;
    try {
        const response = userId.value
            ? await engagementRecordService.userProfile(userId.value, filters)
            : await engagementRecordService.myProfile(filters);
        profile.value = response.data;
    } catch (error) {
        failure.value = error.response?.data?.message || "Unable to load this engagement profile.";
        profile.value = null;
    } finally {
        loading.value = false;
    }
}

async function downloadPdf() {
    downloading.value = true;
    failure.value = "";
    try {
        const response = await engagementRecordService.downloadProfilePdf(userId.value, {
            year: filters.year || undefined,
            engagement_type: filters.engagement_type || undefined,
            sdg: filters.sdg || undefined,
        });
        downloadResponse(response, "engagement-profile.pdf");
    } catch (error) {
        failure.value = error.response?.data?.message || "Unable to download the engagement profile.";
    } finally {
        downloading.value = false;
    }
}

watch(userId, () => fetchProfile(1));
onMounted(() => fetchProfile());
</script>

<template>
  <MainLayout>
    <section class="content">
      <div class="container-fluid py-3">
        <div class="mb-3 d-flex justify-content-between align-items-start gap-3">
          <div>
          <h1 class="h3 mb-1">{{ personName }}</h1>
          <p class="text-muted mb-0">
            {{ profile?.user?.college?.name || "No college assigned" }}
            <span v-if="profile?.user?.role?.name"> · {{ label(profile.user.role.name) }}</span>
          </p>
          </div>
          <button type="button" class="btn btn-success" :disabled="loading || downloading || !profile" @click="downloadPdf">
            <span v-if="downloading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="bi bi-file-earmark-pdf me-1"></i>
            {{ downloading ? "Preparing PDF..." : "Download PDF" }}
          </button>
        </div>

        <div v-if="failure" class="alert alert-danger">{{ failure }}</div>
        <div v-if="loading && !profile" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

        <template v-if="profile">
          <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Verified Engagements</div><div class="display-6 fw-semibold">{{ profile.summary.total_engagements }}</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Service Hours</div><div class="display-6 fw-semibold">{{ Number(profile.summary.total_service_hours).toLocaleString() }}</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Communities Served</div><div class="display-6 fw-semibold">{{ profile.summary.communities_served }}</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Projects Joined</div><div class="display-6 fw-semibold">{{ profile.summary.projects_joined }}</div></div></div></div>
          </div>

          <div class="card mb-3"><div class="card-body"><div class="row g-2">
            <div class="col-md-3"><input v-model="filters.year" type="number" min="1900" max="2100" class="form-control" placeholder="Year"></div>
            <div class="col-md-3"><select v-model="filters.engagement_type" class="form-select"><option value="">All engagement types</option><option v-for="type in engagementTypes" :key="type" :value="type">{{ label(type) }}</option></select></div>
            <div class="col-md-3"><select v-model="filters.sdg" class="form-select"><option value="">All SDGs</option><option v-for="sdg in sdgs" :key="sdg" :value="sdg">{{ label(sdg) }}</option></select></div>
            <div class="col-md-3"><button class="btn btn-primary w-100" :disabled="loading" @click="fetchProfile(1)">Apply Filters</button></div>
          </div></div></div>

          <div class="card">
            <div class="card-header"><h2 class="card-title mb-0">Verified Activities</h2></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
              <thead><tr><th>Activity</th><th>Date</th><th>Role</th><th>Type / SDG</th><th>Project / Community</th><th>Hours</th></tr></thead>
              <tbody>
                <tr v-for="record in profile.engagements.data" :key="record.id">
                  <td><div class="fw-semibold">{{ record.title }}</div><small class="text-muted">{{ record.description || "No description" }}</small></td>
                  <td>{{ record.activity_date }}</td>
                  <td>{{ record.participation_role }}</td>
                  <td>{{ label(record.engagement_type) }}<div class="small text-muted">{{ record.sdg ? label(record.sdg) : "No SDG" }}</div></td>
                  <td>{{ record.project?.title || "No linked project" }}<div class="small text-muted">{{ record.community?.name || "No linked community" }}</div></td>
                  <td>{{ record.service_hours ?? 0 }}</td>
                </tr>
                <tr v-if="profile.engagements.data.length === 0"><td colspan="6" class="text-center text-muted py-5">No verified engagement records found.</td></tr>
              </tbody>
            </table></div></div>
            <div class="card-footer"><CrudPagination :current-page="profile.engagements.current_page" :last-page="profile.engagements.last_page" :prev="Boolean(profile.engagements.prev_page_url)" :next="Boolean(profile.engagements.next_page_url)" @change="fetchProfile" /></div>
          </div>
        </template>
      </div>
    </section>
  </MainLayout>
</template>
