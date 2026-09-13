<script setup>
import { onMounted, reactive, ref } from "vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import communityService from "@/services/communityServices";
import priorityNeedService from "@/services/priorityNeedService";
import { useAuthorization } from "@/composables/useAuthorization";
import { ROLE_GROUPS } from "@/constants/roles";

const { canAccessRoles } = useAuthorization();
const canReview = canAccessRoles(ROLE_GROUPS.PRIORITY_NEED_REVIEWERS);
const notice = ref("");
const failure = ref("");

const rows = ref([]);
const summary = ref([]);
const communities = ref([]);
const pagination = ref({});
const loading = ref(false);

const filters = reactive({
  community_id: "",
  need: "",
  priority_rank: "",
  year: "",
  month: "",
  page: 1,
  per_page: 10,
});

async function loadCommunities() {
  const response = await communityService.getAll();
  communities.value = response.data.data;
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const [listResponse, summaryResponse] =
      await Promise.all([
        priorityNeedService.getList(filters),
        priorityNeedService.getSummary({
          community_id: filters.community_id,
          year: filters.year,
          month: filters.month,
        }),
      ]);

    rows.value = listResponse.data.data.data;
    pagination.value = listResponse.data.data;
    summary.value = summaryResponse.data.data;
  } finally {
    loading.value = false;
  }
}

async function reviewNeed(row, decision) {
  const remarks = decision === "reject"
    ? window.prompt("Reason for rejection:")
    : null;
  if (decision === "reject" && !remarks?.trim()) return;
  try {
    const method = decision === "validate" ? "validate" : "reject";
    const response = await priorityNeedService[method](row.id, remarks);
    notice.value = response.data.message;
    await fetchData(pagination.value.current_page || 1);
  } catch (error) {
    failure.value = error.response?.data?.message || "Unable to review priority need.";
  }
}

onMounted(async () => {
  await loadCommunities();
  await fetchData();
});
</script>

<template>
  <MainLayout>
    <section class="content">
      <div class="container-fluid">
      <div class="mb-3">
        <h1 class="h3 mb-1">Priority Needs</h1>
        <p class="text-muted mb-0">
          Review the highest-priority community needs from submitted surveys.
        </p>
      </div>

      <div v-if="notice" class="alert alert-success">{{ notice }}</div>
      <div v-if="failure" class="alert alert-danger">{{ failure }}</div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-3">
              <select
                v-model="filters.community_id"
                class="form-select"
              >
                <option value="">All communities</option>
                <option
                  v-for="community in communities"
                  :key="community.id"
                  :value="community.id"
                >
                  {{ community.name }}
                </option>
              </select>
            </div>

            <div class="col-md-3">
              <input
                v-model="filters.need"
                class="form-control"
                placeholder="Search need..."
              />
            </div>

            <div class="col-md-2">
              <select
                v-model="filters.priority_rank"
                class="form-select"
              >
                <option value="">All priorities</option>
                <option value="1">Priority 1</option>
                <option value="2">Priority 2</option>
                <option value="3">Priority 3</option>
              </select>
            </div>

            <div class="col-md-1">
              <input
                v-model="filters.year"
                type="number"
                class="form-control"
                placeholder="Year"
              />
            </div>

            <div class="col-md-1">
              <input
                v-model="filters.month"
                type="number"
                min="1"
                max="12"
                class="form-control"
                placeholder="Month"
              />
            </div>

            <div class="col-md-2">
              <button
                class="btn btn-primary w-100"
                @click="fetchData(1)"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div
          v-for="item in summary.slice(0, 4)"
          :key="item.need"
          class="col-md-3"
        >
          <div class="card h-100">
            <div class="card-body">
              <div class="text-muted small">
                {{ item.need }}
              </div>

              <div class="fs-3 fw-bold">
                {{ item.total_count }}
              </div>

              <small class="text-muted">
                Average rank:
                {{
                  Number(
                    item.average_priority_rank
                  ).toFixed(2)
                }}
              </small>
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
                  <th>Priority</th>
                  <th>Need</th>
                  <th>Community</th>
                  <th>Survey Date</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th v-if="canReview" class="text-end">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="row in rows" :key="row.id">
                  <td>
                    <span class="badge text-bg-primary">
                      {{ row.priority_rank }}
                    </span>
                  </td>

                  <td class="fw-semibold">{{ row.need }}</td>

                  <td>{{ row.community?.name }}</td>

                  <td>{{ row.response?.survey_date }}</td>

                  <td>{{ row.description || "—" }}</td>
                  <td><span class="badge text-bg-secondary">{{ row.status || "pending" }}</span></td>
                  <td v-if="canReview" class="text-end text-nowrap">
                    <button v-if="row.status !== 'validated'" class="btn btn-sm btn-outline-success me-1" @click="reviewNeed(row, 'validate')">Validate</button>
                    <button v-if="row.status !== 'rejected'" class="btn btn-sm btn-outline-danger" @click="reviewNeed(row, 'reject')">Reject</button>
                  </td>
                </tr>

                <tr v-if="rows.length === 0">
                  <td :colspan="canReview ? 7 : 6" class="text-center py-5 text-muted">
                    No priority needs found.
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
            @change="fetchData"
          />
        </div>
      </div>
      </div>
    </section>
  </MainLayout>
</template>
