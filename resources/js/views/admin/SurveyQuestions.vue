<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import surveyTemplateService from "@/services/surveyTemplateService";
import surveyQuestionService from "@/services/surveyQuestionService";

const route = useRoute();

const rows = ref([]);
const templates = ref([]);
const pagination = ref({});
const loading = ref(false);
const submitting = ref(false);
const modalRef = ref(null);
const editingId = ref(null);
const errors = ref({});

const filters = reactive({
  search: "",
  survey_template_id:
    route.query.survey_template_id?.toString() ?? "",
  section: "",
  is_active: "",
  page: 1,
  per_page: 10,
});

const defaultForm = () => ({
  survey_template_id: filters.survey_template_id || "",
  section: "",
  question: "",
  question_type: "text",
  options: [],
  is_required: false,
  is_active: true,
  sort_order: 0,
  help_text: "",
});

const form = reactive(defaultForm());
const optionInput = ref("");

const optionBasedTypes = ["select", "radio", "checkbox"];

const usesOptions = computed(() =>
  optionBasedTypes.includes(form.question_type)
);

const isEditing = computed(() => editingId.value !== null);

function resetForm() {
  Object.assign(form, defaultForm());
  editingId.value = null;
  optionInput.value = "";
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
    survey_template_id: row.survey_template_id,
    section: row.section ?? "",
    question: row.question,
    question_type: row.question_type,
    options: Array.isArray(row.options) ? [...row.options] : [],
    is_required: Boolean(row.is_required),
    is_active: Boolean(row.is_active),
    sort_order: row.sort_order ?? 0,
    help_text: row.help_text ?? "",
  });

  modalRef.value?.show();
}

function addOption() {
  const value = optionInput.value.trim();

  if (!value || form.options.includes(value)) {
    return;
  }

  form.options.push(value);
  optionInput.value = "";
}

function removeOption(index) {
  form.options.splice(index, 1);
}

async function loadTemplates() {
  const response = await surveyTemplateService.list({
    per_page: 100,
  });

  templates.value = response.data.data.data;
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const response = await surveyQuestionService.list(filters);

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
    const payload = {
      ...form,
      options: usesOptions.value ? form.options : null,
    };

    if (isEditing.value) {
      delete payload.survey_template_id;
      await surveyQuestionService.update(editingId.value, payload);
    } else {
      await surveyQuestionService.create(payload);
    }

    modalRef.value?.hide();
    resetForm();
    await fetchData(filters.page);
  } catch (error) {
    errors.value = error.response?.data?.errors ?? {};

    if (!error.response?.data?.errors) {
      window.alert(
        error.response?.data?.message ??
          "Unable to save survey question."
      );
    }
  } finally {
    submitting.value = false;
  }
}

async function deleteQuestion(row) {
  if (!window.confirm("Delete this survey question?")) {
    return;
  }

  try {
    await surveyQuestionService.remove(row.id);
    await fetchData(filters.page);
  } catch (error) {
    window.alert(
      error.response?.data?.message ??
        "Unable to delete survey question."
    );
  }
}

watch(
  () => form.question_type,
  (type) => {
    if (!optionBasedTypes.includes(type)) {
      form.options = [];
    }
  }
);

onMounted(async () => {
  await Promise.all([loadTemplates(), fetchData()]);
});
</script>

<template>
  <section class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h3 mb-1">Survey Questions</h1>
          <p class="text-muted mb-0">
            Configure the questions shown in each survey template.
          </p>
        </div>

        <button class="btn btn-primary" @click="openCreate">
          <i class="bi bi-plus-circle me-1"></i>
          Add Question
        </button>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-4">
              <input
                v-model="filters.search"
                class="form-control"
                placeholder="Search question..."
                @keyup.enter="fetchData(1)"
              />
            </div>

            <div class="col-md-3">
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
                  {{ template.title }} v{{ template.version }}
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <input
                v-model="filters.section"
                class="form-control"
                placeholder="Section"
              />
            </div>

            <div class="col-md-2">
              <select v-model="filters.is_active" class="form-select">
                <option value="">All statuses</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
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
                  <th style="width: 70px">Order</th>
                  <th>Question</th>
                  <th>Template</th>
                  <th>Section</th>
                  <th>Type</th>
                  <th>Required</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="row in rows" :key="row.id">
                  <td>{{ row.sort_order }}</td>

                  <td>
                    <div class="fw-semibold">
                      {{ row.question }}
                    </div>
                    <small
                      v-if="row.help_text"
                      class="text-muted"
                    >
                      {{ row.help_text }}
                    </small>
                  </td>

                  <td>
                    {{ row.template?.title }}
                    <small class="d-block text-muted">
                      Version {{ row.template?.version }}
                    </small>
                  </td>

                  <td>{{ row.section || "General" }}</td>

                  <td>
                    <span class="badge text-bg-light text-capitalize">
                      {{ row.question_type }}
                    </span>
                  </td>

                  <td>
                    <i
                      v-if="row.is_required"
                      class="bi bi-check-circle-fill text-success"
                    ></i>
                    <span v-else>—</span>
                  </td>

                  <td>
                    <span
                      class="badge"
                      :class="
                        row.is_active
                          ? 'text-bg-success'
                          : 'text-bg-secondary'
                      "
                    >
                      {{ row.is_active ? "Active" : "Inactive" }}
                    </span>
                  </td>

                  <td class="text-end">
                    <button
                      class="btn btn-sm btn-outline-primary me-1"
                      @click="openEdit(row)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>

                    <button
                      class="btn btn-sm btn-outline-danger"
                      @click="deleteQuestion(row)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>

                <tr v-if="rows.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">
                    No survey questions found.
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
        :title="isEditing ? 'Edit Survey Question' : 'Add Survey Question'"
        size="lg"
        @hidden="resetForm"
      >
        <div class="row g-3">
          <div v-if="!isEditing" class="col-12">
            <label class="form-label">Survey template</label>
            <select
              v-model="form.survey_template_id"
              class="form-select"
              :class="{
                'is-invalid': errors.survey_template_id,
              }"
            >
              <option value="">Select template</option>
              <option
                v-for="template in templates"
                :key="template.id"
                :value="template.id"
              >
                {{ template.title }} v{{ template.version }}
              </option>
            </select>
            <div class="invalid-feedback">
              {{ errors.survey_template_id?.[0] }}
            </div>
          </div>

          <div class="col-md-8">
            <label class="form-label">Section</label>
            <input
              v-model="form.section"
              class="form-control"
              placeholder="Example: Basic Community Profile"
              :class="{ 'is-invalid': errors.section }"
            />
            <div class="invalid-feedback">
              {{ errors.section?.[0] }}
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Sort order</label>
            <input
              v-model.number="form.sort_order"
              type="number"
              min="0"
              class="form-control"
              :class="{ 'is-invalid': errors.sort_order }"
            />
            <div class="invalid-feedback">
              {{ errors.sort_order?.[0] }}
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Question</label>
            <textarea
              v-model="form.question"
              rows="3"
              class="form-control"
              :class="{ 'is-invalid': errors.question }"
            ></textarea>
            <div class="invalid-feedback">
              {{ errors.question?.[0] }}
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Question type</label>
            <select
              v-model="form.question_type"
              class="form-select"
              :class="{ 'is-invalid': errors.question_type }"
            >
              <option value="text">Text</option>
              <option value="textarea">Textarea</option>
              <option value="number">Number</option>
              <option value="date">Date</option>
              <option value="select">Select</option>
              <option value="radio">Radio</option>
              <option value="checkbox">Checkbox</option>
              <option value="boolean">Yes / No</option>
            </select>
            <div class="invalid-feedback">
              {{ errors.question_type?.[0] }}
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Help text</label>
            <input
              v-model="form.help_text"
              class="form-control"
              :class="{ 'is-invalid': errors.help_text }"
            />
            <div class="invalid-feedback">
              {{ errors.help_text?.[0] }}
            </div>
          </div>

          <div v-if="usesOptions" class="col-12">
            <label class="form-label">Options</label>

            <div class="input-group mb-2">
              <input
                v-model="optionInput"
                class="form-control"
                placeholder="Enter option"
                @keyup.enter.prevent="addOption"
              />

              <button
                type="button"
                class="btn btn-outline-primary"
                @click="addOption"
              >
                Add
              </button>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <span
                v-for="(option, index) in form.options"
                :key="option"
                class="badge rounded-pill text-bg-light border"
              >
                {{ option }}

                <button
                  type="button"
                  class="btn-close ms-2"
                  style="font-size: 0.55rem"
                  @click="removeOption(index)"
                ></button>
              </span>
            </div>

            <div
              v-if="errors.options"
              class="text-danger small mt-2"
            >
              {{ errors.options?.[0] }}
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch">
              <input
                id="question-required"
                v-model="form.is_required"
                class="form-check-input"
                type="checkbox"
              />
              <label
                class="form-check-label"
                for="question-required"
              >
                Required question
              </label>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch">
              <input
                id="question-active"
                v-model="form.is_active"
                class="form-check-input"
                type="checkbox"
              />
              <label
                class="form-check-label"
                for="question-active"
              >
                Active
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
            Save Question
          </button>
        </template>
      </CrudModal>
    </div>
  </section>
</template>
