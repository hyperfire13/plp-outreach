<template>
  <MainLayout>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Colleges</h4>
        <button class="btn btn-primary btn-sm" @click="openCreate">
          <i class="fas fa-plus me-1"></i> Add College
        </button>
      </div>

      <div class="card-body">

        <!-- TABLE -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Location</th>
                <th width="160">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="college in colleges.data" :key="college.id">
                <td>{{ college.name }}</td>
                <td>{{ college.type }}</td>
                <td>{{ college.location }}</td>
                <td>
                  <button
                    class="btn btn-sm btn-warning me-1"
                    @click="openEdit(college)"
                  >
                    Edit
                  </button>

                  <button
                    class="btn btn-sm btn-danger"
                    @click="remove(college.id)"
                  >
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="!colleges.data || colleges.data.length === 0">
                <td colspan="4" class="text-center text-muted">
                  No colleges found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div>
            Page {{ colleges.current_page || 1 }}
            of {{ colleges.last_page || 1 }}
          </div>

          <div>
            <button
              class="btn btn-sm btn-secondary me-2"
              :disabled="!colleges.prev_page_url"
              @click="fetch(colleges.current_page - 1)"
            >
              Previous
            </button>

            <button
              class="btn btn-sm btn-secondary"
              :disabled="!colleges.next_page_url"
              @click="fetch(colleges.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- MODAL -->
    <div
      class="modal fade"
      id="collegeModal"
      tabindex="-1"
      ref="modalRef"
    >
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEdit ? 'Edit College' : 'Create College' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <div class="modal-body">

            <div class="mb-3">
              <label class="form-label">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="form-control"
              />
              <small class="text-danger">{{ errors.name }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Type</label>
              <input
                v-model="form.type"
                type="text"
                class="form-control"
              />
              <small class="text-danger">{{ errors.type }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Location</label>
              <input
                v-model="form.location"
                type="text"
                class="form-control"
              />
              <small class="text-danger">{{ errors.location }}</small>
            </div>

          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">
              Cancel
            </button>

            <button class="btn btn-primary" @click="submit">
              {{ isEdit ? 'Update' : 'Save' }}
            </button>
          </div>

        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import MainLayout from '../components/layout/MainLayout.vue'
import collegeService from '../services/collegeService'

const colleges = ref({ data: [] })
const modalRef = ref(null)
let modalInstance = null

const isEdit = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  type: '',
  location: ''
})

const errors = reactive({})

/* =========================
   FETCH DATA
========================= */
const fetch = async (page = 1) => {
  const response = await collegeService.get(page)
  colleges.value = response.data
}

/* =========================
   MODAL CONTROL
========================= */
const openCreate = () => {
  resetForm()
  isEdit.value = false
  modalInstance.show()
}

const openEdit = (college) => {
  resetForm()
  isEdit.value = true
  editingId.value = college.id

  form.name = college.name
  form.type = college.type
  form.location = college.location

  modalInstance.show()
}

const closeModal = () => {
  modalInstance.hide()
}

/* =========================
   SUBMIT
========================= */
const submit = async () => {
  clearErrors()

  try {
    if (isEdit.value) {
      await collegeService.update(editingId.value, form)
    } else {
      await collegeService.store(form)
    }

    closeModal()
    fetch()

  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    }
  }
}

/* =========================
   DELETE
========================= */
const remove = async (id) => {
  if (!confirm('Are you sure you want to delete this college?')) return

  await collegeService.delete(id)
  fetch()
}

/* =========================
   HELPERS
========================= */
const resetForm = () => {
  form.name = ''
  form.type = ''
  form.location = ''
  editingId.value = null
  clearErrors()
}

const clearErrors = () => {
  Object.keys(errors).forEach(key => delete errors[key])
}

/* =========================
   LIFECYCLE
========================= */
onMounted(() => {
  modalInstance = new Modal(modalRef.value)
  fetch()
})
</script>