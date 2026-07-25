<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import surveyTemplateService from "@/services/surveyTemplateService";


const router = useRouter();

const rows = ref([]);
const pagination = ref({});
const loading = ref(false);
const submitting = ref(false);
const modalRef = ref(null);
const editingId = ref(null);
const errors = ref({});

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

const isEditing = computed(() => editingId.value !== null);

function resetForm() {
  Object.assign(form, defaultForm());
  editingId.value = null;
  errors.value = {};
}

function openCreate() {
  resetForm();
  modalRef.value?.show();
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

  modalRef.value?.show();
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const response = await surveyTemplateService.list(filters);

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

    modalRef.value?.hide();
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

onMounted(fetchData);
</script>

<template>
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
            :pagination="pagination"
            @change="fetchData"
          />
        </div>
      </div>

      <CrudModal
        ref="modalRef"
        :title="isEditing ? 'Edit Survey Template' : 'Add Survey Template'"
        size="lg"
        @hidden="resetForm"
      >
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Template title</label>
            <input
              v-model="form.title"
              class="form-control"
              :class="{ 'is-invalid': errors.title }"
            />
            <div class="invalid-feedback">
              {{ errors.title?.[0] }}
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Version</label>
            <input
              v-model.number="form.version"
              type="number"
              min="1"
              class="form-control"
              :class="{ 'is-invalid': errors.version }"
            />
            <div class="invalid-feedback">
              {{ errors.version?.[0] }}
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="form-control"
              :class="{ 'is-invalid': errors.description }"
            ></textarea>
            <div class="invalid-feedback">
              {{ errors.description?.[0] }}
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select
              v-model="form.status"
              class="form-select"
              :class="{ 'is-invalid': errors.status }"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
            <div class="invalid-feedback">
              {{ errors.status?.[0] }}
            </div>
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
              <input
                id="default-template"
                v-model="form.is_default"
                class="form-check-input"
                type="checkbox"
              />
              <label
                class="form-check-label"
                for="default-template"
              >
                Set as default template
              </label>
            </div>
          </div>
        </div>

        <template #footer>
          <button
            class="btn btn-secondary"
            @click="modalRef?.hide()"
          >
            Cancel
          </button>

          <button
            class="btn btn-primary"
            :disabled="submitting"
            @click="submitForm"
          >
            <span
              v-if="submitting"
              class="spinner-border spinner-border-sm me-1"
            ></span>
            Save Template
          </button>
        </template>
      </CrudModal>
    </div>
  </section>
</template>
