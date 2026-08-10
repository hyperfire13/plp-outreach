<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import CrudModal from "../../components/crud/CrudModal.vue";
import CrudPagination from "../../components/crud/CrudPagination.vue";
import communityService from "@/services/communityServices";
import MainLayout from '../../components/layout/MainLayout.vue'

const rows = ref([]);
const pagination = ref({});
const loading = ref(false);
const submitting = ref(false);
const errors = ref({})

const modalRef = ref(null);
const editingId = ref(null);

const fields = computed(() => [
    {
        key: 'name',
        label: 'Community Name',
        type: 'text',
        required: true,
        col: 'col-md-6 col-12'
    },
    {
        key: 'barangay_code',
        label: 'Barangay Code',
        type: 'text',
        col: 'col-md-6 col-12'
    },
    {
        key: 'city',
        label: 'City',
        type: 'text',
        col: 'col-md-6 col-12'
    },
    {
        key: 'province',
        label: 'Province',
        type: 'text',
        col: 'col-md-6 col-12'
    },
    {
        key: 'estimated_population',
        label: 'Estimated Population',
        type: 'number',
        col: 'col-md-6 col-12'
    },
    {
        key: 'estimated_households',
        label: 'Estimated Households',
        type: 'number',
        col: 'col-md-6 col-12'
    },
    {
        key: 'community_type',
        label: 'Community Type',
        type: 'select',
        options: [
            { value: 'urban', label: 'Urban' },
            { value: 'rural', label: 'Rural' },
            { value: 'mixed', label: 'Mixed' }
        ],
        col: 'col-md-6 col-12'
    },
    {
        key: 'predominant_livelihood',
        label: 'Predominant Livelihood',
        type: 'text',
        col: 'col-md-6 col-12'
    },
    {
        key: 'address',
        label: 'Address',
        type: 'textarea',
        rows: 2,
        col: 'col-12'
    },
    {
        key: 'remarks',
        label: 'Remarks',
        type: 'textarea',
        rows: 3,
        col: 'col-12'
    },
    {
        key: 'is_active',
        label: 'Active',
        type: 'checkbox',
        checkboxLabel: 'Community is active',
        col: 'col-12'
    }
])

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
  modalRef.value?.open();
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



  modalRef.value?.open();
}

async function fetchData(page = 1) {
  loading.value = true;

  try {
    filters.page = page;

    const response = await communityService.getList(filters);

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
      await communityService.update(editingId.value, payload);
    } else {
      await communityService.create(payload);
    }

    modalRef.value?.close();
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
    await communityService.remove(row.id);
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
  <MainLayout>
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
            :title="modalTitle"
            size="lg"
            @hidden="resetForm"
            :fields="fields"
            :form="form"
            :errors="errors"
            :submit-label="isEditing ? 'Update' : 'Save'"
            @submit="submitForm"
        >
        </CrudModal>
        </div>
    </section>
  </MainLayout>
</template>
