<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import surveyTemplateService from "../../services/surveyTemplateService";
import MainLayout from '../../components/layout/MainLayout.vue'
import { downloadResponse } from "@/utils/downloadResponse";



const router = useRouter();

const rows = ref([]);
const pagination = ref({});
const loading = ref(false);
const submitting = ref(false);
const modalRef = ref(null);
const editingId = ref(null);
const errors = ref({});
const downloadingId = ref(null);


const filters = reactive({
  search: "",
  status: "",
  page: 1,
  per_page: 10,
});

const defaultForm = () => ({
  title: "",
  description: "",
  version: 1,
  status: "draft",
  is_default: false,
});

const form = reactive(defaultForm());

const fields = computed(() => [
    {
        key: 'title',
        label: 'Template Title',
        type: 'text',
        required: true,
        col: 'col-md-8 col-12'
    },
    {
        key: 'version',
        label: 'Version',
        type: 'number',
        required: true,
        min: 1,
        col: 'col-md-4 col-12'
    },
    {
        key: 'description',
        label: 'Description',
        type: 'textarea',
        rows: 4,
        col: 'col-12'
    },
    {
        key: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: [
            {
                value: 'draft',
                label: 'Draft'
            },
            {
                value: 'published',
                label: 'Published'
            },
            {
                value: 'archived',
                label: 'Archived'
            }
        ],
        col: 'col-md-6 col-12'
    },
    {
        key: 'is_default',
        label: 'Default Template',
        type: 'checkbox',
        checkboxLabel: 'Set as default template',
        col: 'col-md-6 col-12'
    }
]);

const isEditing = computed(() => editingId.value !== null);

function resetForm() {
  Object.assign(form, defaultForm());
  editingId.value = null;
  errors.value = {};
}

function openCreate() {
  resetForm();
  modalRef.value?.open();
}

function openEdit(row) {
  resetForm();
  editingId.value = row.id;

  Object.assign(form, {
    title: row.title,
    description: row.description ?? "",
    version: row.version,
    status: row.status,
    is_default: Boolean(row.is_default),
  });

  modalRef.value?.open();
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const response = await surveyTemplateService.getList(filters);

    rows.value = response.data.data.data;
    pagination.value = response.data.data;
  } finally {
    loading.value = false;
  }
}

async function submitForm() {
  submitting.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await surveyTemplateService.update(editingId.value, form);
    } else {
      await surveyTemplateService.create(form);
    }

    modalRef.value?.close();
    resetForm();
    await fetchData(filters.page);
  } catch (error) {
    errors.value = error.response?.data?.errors ?? {};

    if (!error.response?.data?.errors) {
      window.alert(
        error.response?.data?.message ??
          "Unable to save survey template."
      );
    }
  } finally {
    submitting.value = false;
  }
}

async function deleteTemplate(row) {
  if (!window.confirm(`Delete template "${row.title}"?`)) {
    return;
  }

  try {
    await surveyTemplateService.remove(row.id);
    await fetchData(filters.page);
  } catch (error) {
    window.alert(
      error.response?.data?.message ??
        "Unable to delete the survey template."
    );
  }
}

function manageQuestions(row) {
  router.push({
    name: "admin-survey-questions",
    query: {
      survey_template_id: row.id,
    },
  });
}

async function downloadBlankForm(row) {
  if (downloadingId.value !== null) {
    return;
  }

  downloadingId.value = row.id;

  try {
    const response = await surveyTemplateService.downloadPdf(row.id);
    downloadResponse(response, `survey-template-v${row.version}.pdf`);
  } catch (error) {
    window.alert(
      error.response?.data?.message ??
        "Unable to download the blank survey form."
    );
  } finally {
    downloadingId.value = null;
  }
}

onMounted(fetchData);
</script>

<template>
  <MainLayout>
    <section class="content">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h1 class="h3 mb-1">Survey Templates</h1>
            <p class="text-muted mb-0">
              Manage survey forms, versions, and publication status.
            </p>
          </div>

          <button class="btn btn-primary" @click="openCreate">
            <i class="bi bi-plus-circle me-1"></i>
            Add Template
          </button>
        </div>

        <div class="card mb-3">
          <div class="card-body">
            <div class="row g-2">
              <div class="col-md-7">
                <input
                  v-model="filters.search"
                  class="form-control"
                  placeholder="Search template..."
                  @keyup.enter="fetchData(1)"
                />
              </div>

              <div class="col-md-3">
                <select v-model="filters.status" class="form-select">
                  <option value="">All statuses</option>
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-2">
                <button
                  class="btn btn-primary w-100"
                  @click="fetchData(1)"
                >
                  Search
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
                    <th>Template</th>
                    <th>Version</th>
                    <th>Questions</th>
                    <th>Responses</th>
                    <th>Status</th>
                    <th>Default</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="row in rows" :key="row.id">
                    <td>
                      <div class="fw-semibold">{{ row.title }}</div>
                      <small class="text-muted">
                        {{ row.description || "No description" }}
                      </small>
                    </td>

                    <td>v{{ row.version }}</td>
                    <td>{{ row.questions_count ?? 0 }}</td>
                    <td>{{ row.responses_count ?? 0 }}</td>

                    <td>
                      <span
                        class="badge text-capitalize"
                        :class="{
                          'text-bg-secondary': row.status === 'draft',
                          'text-bg-success': row.status === 'published',
                          'text-bg-dark': row.status === 'archived',
                        }"
                      >
                        {{ row.status }}
                      </span>
                    </td>

                    <td>
                      <span
                        v-if="row.is_default"
                        class="badge text-bg-primary"
                      >
                        Default
                      </span>
                      <span v-else>—</span>
                    </td>

                    <td class="text-end">
                      <button
                        class="btn btn-sm btn-outline-secondary me-1"
                        title="Download blank survey form"
                        :disabled="downloadingId !== null"
                        @click="downloadBlankForm(row)"
                      >
                        <span
                          v-if="downloadingId === row.id"
                          class="spinner-border spinner-border-sm"
                        ></span>
                        <i v-else class="bi bi-file-earmark-pdf"></i>
                      </button>

                      <button
                        class="btn btn-sm btn-outline-success me-1"
                        title="Manage questions"
                        @click="manageQuestions(row)"
                      >
                        <i class="bi bi-list-check"></i>
                      </button>

                      <button
                        class="btn btn-sm btn-outline-primary me-1"
                        @click="openEdit(row)"
                      >
                        <i class="bi bi-pencil"></i>
                      </button>

                      <button
                        class="btn btn-sm btn-outline-danger"
                        @click="deleteTemplate(row)"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>

                  <tr v-if="rows.length === 0">
                    <td colspan="7" class="text-center py-5 text-muted">
                      No survey templates found.
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

        <CrudModal
          ref="modalRef"
          :title="isEditing ? 'Edit Survey Template' : 'Add Survey Template'"
          :fields="fields"
          :form="form"
          size="lg"
          @hidden="resetForm"

          :errors="errors"
          :submit-label="isEditing ? 'Update' : 'Save'"
          @submit="submitForm"
        >
        </CrudModal>
      </div>
    </section>
  </MainLayout>
</template>
