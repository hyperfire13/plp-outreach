<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import CommunityService from "@/services/CommunityService";

const rows = ref([]);
const pagination = ref({});
const loading = ref(false);
const submitting = ref(false);
const errors = ref({});

const modalRef = ref(null);
const editingId = ref(null);

const filters = reactive({
  search: "",
  community_type: "",
  is_active: "",
  page: 1,
  per_page: 10,
});

const defaultForm = () => ({
  name: "",
  barangay_code: "",
  city: "Pasig City",
  province: "Metro Manila",
  estimated_population: null,
  estimated_households: null,
  community_type: "",
  predominant_livelihood: "",
  address: "",
  remarks: "",
  is_active: true,
});

const form = reactive(defaultForm());

const isEditing = computed(() => editingId.value !== null);

const modalTitle = computed(() =>
  isEditing.value ? "Edit Community" : "Add Community"
);

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
    name: row.name ?? "",
    barangay_code: row.barangay_code ?? "",
    city: row.city ?? "Pasig City",
    province: row.province ?? "",
    estimated_population: row.estimated_population,
    estimated_households: row.estimated_households,
    community_type: row.community_type ?? "",
    predominant_livelihood: row.predominant_livelihood ?? "",
    address: row.address ?? "",
    remarks: row.remarks ?? "",
    is_active: Boolean(row.is_active),
  });

  modalRef.value?.show();
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const response = await CommunityService.list(filters);

    rows.value = response.data.data.data;
    pagination.value = response.data.data;
  } catch (error) {
    console.error("Unable to load communities.", error);
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
      estimated_population:
        form.estimated_population === ""
          ? null
          : form.estimated_population,
      estimated_households:
        form.estimated_households === ""
          ? null
          : form.estimated_households,
    };

    if (isEditing.value) {
      await CommunityService.update(editingId.value, payload);
    } else {
      await CommunityService.create(payload);
    }

    modalRef.value?.hide();
    resetForm();
    await fetchData(filters.page);
  } catch (error) {
    errors.value = error.response?.data?.errors ?? {};

    if (!error.response?.data?.errors) {
      console.error("Unable to save community.", error);
    }
  } finally {
    submitting.value = false;
  }
}

async function deleteCommunity(row) {
  const confirmed = window.confirm(
    `Delete community "${row.name}"?`
  );

  if (!confirmed) {
    return;
  }

  try {
    await CommunityService.remove(row.id);
    await fetchData(filters.page);
  } catch (error) {
    const message =
      error.response?.data?.message ??
      "Unable to delete the community.";

    window.alert(message);
  }
}

function clearFilters() {
  filters.search = "";
  filters.community_type = "";
  filters.is_active = "";
  fetchData(1);
}

onMounted(() => {
  fetchData();
});
</script>

<template>
  <section class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h3 mb-1">Communities</h1>
          <p class="text-muted mb-0">
            Manage barangays and community profile information.
          </p>
        </div>

        <button class="btn btn-primary" @click="openCreate">
          <i class="bi bi-plus-circle me-1"></i>
          Add Community
        </button>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-5">
              <input
                v-model="filters.search"
                type="text"
                class="form-control"
                placeholder="Search community..."
                @keyup.enter="fetchData(1)"
              />
            </div>

            <div class="col-md-3">
              <select
                v-model="filters.community_type"
                class="form-select"
              >
                <option value="">All community types</option>
                <option value="urban">Urban</option>
                <option value="rural">Rural</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>

            <div class="col-md-2">
              <select v-model="filters.is_active" class="form-select">
                <option value="">All statuses</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
              <button
                class="btn btn-primary flex-fill"
                @click="fetchData(1)"
              >
                Search
              </button>

              <button
                class="btn btn-outline-secondary"
                @click="clearFilters"
              >
                Clear
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
                  <th>Location</th>
                  <th>Population</th>
                  <th>Households</th>
                  <th>Type</th>
                  <th>Responses</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="row in rows" :key="row.id">
                  <td>
                    <div class="fw-semibold">{{ row.name }}</div>
                    <small class="text-muted">
                      {{ row.barangay_code || "No barangay code" }}
                    </small>
                  </td>

                  <td>
                    {{ row.city }}
                    <small
                      v-if="row.province"
                      class="d-block text-muted"
                    >
                      {{ row.province }}
                    </small>
                  </td>

                  <td>
                    {{
                      row.estimated_population?.toLocaleString() ?? "—"
                    }}
                  </td>

                  <td>
                    {{
                      row.estimated_households?.toLocaleString() ?? "—"
                    }}
                  </td>

                  <td class="text-capitalize">
                    {{ row.community_type || "—" }}
                  </td>

                  <td>{{ row.survey_responses_count ?? 0 }}</td>

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
                      @click="deleteCommunity(row)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>

                <tr v-if="rows.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">
                    No communities found.
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
        :title="modalTitle"
        size="lg"
        @hidden="resetForm"
      >
        <form @submit.prevent="submitForm">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Community name</label>
              <input
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
              />
              <div class="invalid-feedback">
                {{ errors.name?.[0] }}
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Barangay code</label>
              <input
                v-model="form.barangay_code"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.barangay_code }"
              />
              <div class="invalid-feedback">
                {{ errors.barangay_code?.[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">City</label>
              <input
                v-model="form.city"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.city }"
              />
              <div class="invalid-feedback">
                {{ errors.city?.[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Province</label>
              <input
                v-model="form.province"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.province }"
              />
              <div class="invalid-feedback">
                {{ errors.province?.[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Estimated population</label>
              <input
                v-model.number="form.estimated_population"
                type="number"
                min="0"
                class="form-control"
                :class="{
                  'is-invalid': errors.estimated_population,
                }"
              />
              <div class="invalid-feedback">
                {{ errors.estimated_population?.[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Estimated households</label>
              <input
                v-model.number="form.estimated_households"
                type="number"
                min="0"
                class="form-control"
                :class="{
                  'is-invalid': errors.estimated_households,
                }"
              />
              <div class="invalid-feedback">
                {{ errors.estimated_households?.[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Community type</label>
              <select
                v-model="form.community_type"
                class="form-select"
                :class="{ 'is-invalid': errors.community_type }"
              >
                <option value="">Select type</option>
                <option value="urban">Urban</option>
                <option value="rural">Rural</option>
                <option value="mixed">Mixed</option>
              </select>
              <div class="invalid-feedback">
                {{ errors.community_type?.[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">
                Predominant livelihood
              </label>
              <input
                v-model="form.predominant_livelihood"
                type="text"
                class="form-control"
                :class="{
                  'is-invalid': errors.predominant_livelihood,
                }"
              />
              <div class="invalid-feedback">
                {{ errors.predominant_livelihood?.[0] }}
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea
                v-model="form.address"
                rows="2"
                class="form-control"
                :class="{ 'is-invalid': errors.address }"
              ></textarea>
              <div class="invalid-feedback">
                {{ errors.address?.[0] }}
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Remarks</label>
              <textarea
                v-model="form.remarks"
                rows="3"
                class="form-control"
                :class="{ 'is-invalid': errors.remarks }"
              ></textarea>
              <div class="invalid-feedback">
                {{ errors.remarks?.[0] }}
              </div>
            </div>

            <div class="col-12">
              <div class="form-check form-switch">
                <input
                  id="community-active"
                  v-model="form.is_active"
                  class="form-check-input"
                  type="checkbox"
                />
                <label
                  class="form-check-label"
                  for="community-active"
                >
                  Active
                </label>
              </div>
            </div>
          </div>
        </form>

        <template #footer>
          <button
            type="button"
            class="btn btn-secondary"
            @click="modalRef?.hide()"
          >
            Cancel
          </button>

          <button
            type="button"
            class="btn btn-primary"
            :disabled="submitting"
            @click="submitForm"
          >
            <span
              v-if="submitting"
              class="spinner-border spinner-border-sm me-1"
            ></span>
            Save Community
          </button>
        </template>
      </CrudModal>
    </div>
  </section>
</template>
