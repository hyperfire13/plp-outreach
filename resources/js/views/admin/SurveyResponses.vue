<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import communityService from "@/services/communityService";
import surveyResponseService from "@/services/surveyResponseService";
import surveyTemplateService from "@/services/surveyTemplateService";

const router = useRouter();

const rows = ref([]);
const communities = ref([]);
const templates = ref([]);
const pagination = ref({});
const loading = ref(false);

const filters = reactive({
  search: "",
  community_id: "",
  survey_template_id: "",
  status: "",
  year: "",
  month: "",
  page: 1,
  per_page: 10,
});

function formatDate(value) {
  if (!value) {
    return "—";
  }

  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(new Date(value));
}

async function fetchOptions() {
  const [communityResponse, templateResponse] =
    await Promise.all([
      communityService.all(),
      surveyTemplateService.list({
        per_page: 100,
      }),
    ]);

  communities.value = communityResponse.data.data;
  templates.value = templateResponse.data.data.data;
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const response = await surveyResponseService.list(filters);

    rows.value = response.data.data.data;
    pagination.value = response.data.data;
  } finally {
    loading.value = false;
  }
}

function createResponse() {
  router.push({
    name: "admin-survey-responses-create",
  });
}

function viewResponse(row) {
  router.push({
    name: "admin-survey-responses-view",
    params: {
      id: row.id,
    },
  });
}

function editResponse(row) {
  router.push({
    name: "admin-survey-responses-edit",
    params: {
      id: row.id,
    },
  });
}

async function deleteResponse(row) {
  if (!window.confirm("Delete this draft survey response?")) {
    return;
  }

  try {
    await surveyResponseService.remove(row.id);
    await fetchData(filters.page);
  } catch (error) {
    window.alert(
      error.response?.data?.message ??
        "Unable to delete survey response."
    );
  }
}

onMounted(async () => {
  await Promise.all([fetchOptions(), fetchData()]);
});
</script>

<template>
  <section class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h3 mb-1">Survey Responses</h1>
          <p class="text-muted mb-0">
            Manage encoded community surveys and submissions.
          </p>
        </div>

        <button class="btn btn-primary" @click="createResponse">
          <i class="bi bi-plus-circle me-1"></i>
          Conduct Survey
        </button>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-3">
              <input
                v-model="filters.search"
                class="form-control"
                placeholder="Search response..."
                @keyup.enter="fetchData(1)"
              />
            </div>

            <div class="col-md-2">
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

            <div class="col-md-2">
              <select
                v-model="filters.survey_template_id"
                class="form-select"
              >
                <option value="">All templates</option>
                <option
                  v-for="template in templates"
                  :key="template.id"
                  :value="template.id"
                >
                  {{ template.title }}
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <select v-model="filters.status" class="form-select">
                <option value="">All statuses</option>
                <option value="draft">Draft</option>
                <option value="submitted">Submitted</option>
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

            <div class="col-md-1">
              <button
                class="btn btn-primary w-100"
                @click="fetchData(1)"
              >
                <i class="bi bi-search"></i>
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
                  <th>Community</th>
                  <th>Template</th>
                  <th>Survey Date</th>
                  <th>Department</th>
                  <th>Answers</th>
                  <th>Needs</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="row in rows" :key="row.id">
                  <td>{{ row.community?.name }}</td>

                  <td>
                    {{ row.template?.title }}
                    <small class="d-block text-muted">
                      Version {{ row.template?.version }}
                    </small>
                  </td>

                  <td>{{ formatDate(row.survey_date) }}</td>

                  <td>
                    {{ row.academic_department || "—" }}
                  </td>

                  <td>{{ row.answers_count ?? 0 }}</td>
                  <td>{{ row.priority_needs_count ?? 0 }}</td>

                  <td>
                    <span
                      class="badge text-capitalize"
                      :class="
                        row.status === 'submitted'
                          ? 'text-bg-success'
                          : 'text-bg-secondary'
                      "
                    >
                      {{ row.status }}
                    </span>
                  </td>

                  <td class="text-end">
                    <button
                      class="btn btn-sm btn-outline-info me-1"
                      @click="viewResponse(row)"
                    >
                      <i class="bi bi-eye"></i>
                    </button>

                    <button
                      v-if="row.status === 'draft'"
                      class="btn btn-sm btn-outline-primary me-1"
                      @click="editResponse(row)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>

                    <button
                      v-if="row.status === 'draft'"
                      class="btn btn-sm btn-outline-danger"
                      @click="deleteResponse(row)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>

                <tr v-if="rows.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">
                    No survey responses found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer">
          <CrudPagination
            :pagination="pagination"
            @change="fetchData"
          />
        </div>
      </div>
    </div>
  </section>
</template>
