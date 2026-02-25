<template>
  <MainLayout>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Users</h4>

        <button
          v-if="canCreate"
          class="btn btn-primary btn-sm"
          @click="openCreate"
        >
          <i class="fas fa-plus me-1"></i> Add User
        </button>
      </div>

      <div class="card-body">

        <!-- TABLE -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>College</th>
                <th width="170">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users.data" :key="user.id">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.role?.name }}</td>
                <td>{{ user.college?.name }}</td>
                <td>
                  <button
                    class="btn btn-sm btn-warning me-1"
                    @click="openEdit(user)"
                  >
                    Edit
                  </button>

                  <button
                    v-if="canDelete"
                    class="btn btn-sm btn-danger"
                    @click="remove(user.id)"
                  >
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="!users.data || users.data.length === 0">
                <td colspan="5" class="text-center text-muted">
                  No users found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div>
            Page {{ users.current_page || 1 }}
            of {{ users.last_page || 1 }}
          </div>

          <div>
            <button
              class="btn btn-sm btn-secondary me-2"
              :disabled="!users.prev_page_url"
              @click="fetch(users.current_page - 1)"
            >
              Previous
            </button>

            <button
              class="btn btn-sm btn-secondary"
              :disabled="!users.next_page_url"
              @click="fetch(users.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- USER MODAL -->
    <div class="modal fade" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEdit ? 'Edit User' : 'Create User' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <div class="modal-body">

            <div class="mb-3">
              <label class="form-label">Name</label>
              <input v-model="form.name" class="form-control" />
              <small class="text-danger">{{ errors.name }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="form.email" class="form-control" />
              <small class="text-danger">{{ errors.email }}</small>
            </div>

            <div class="mb-3" v-if="!isEdit">
              <label class="form-label">Password</label>
              <input v-model="form.password" type="password" class="form-control" />
              <small class="text-danger">{{ errors.password }}</small>
            </div>

            <div class="mb-3" v-if="!isEdit">
              <label class="form-label">Confirm Password</label>
              <input v-model="form.password_confirmation" type="password" class="form-control" />
            </div>

            <div class="mb-3">
              <label class="form-label">Role</label>
              <select v-model="form.role_id" class="form-control">
                <option value="">Select Role</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.name }}
                </option>
              </select>
              <small class="text-danger">{{ errors.role_id }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">College</label>
              <select v-model="form.college_id" class="form-control">
                <option value="">Select College</option>
                <option v-for="college in collegesList" :key="college.id" :value="college.id">
                  {{ college.name }}
                </option>
              </select>
            </div>

          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Cancel</button>
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
import { ref, reactive, onMounted, computed } from 'vue'
import { Modal } from 'bootstrap'
import MainLayout from '../components/layout/MainLayout.vue'
import userService from '../services/userService'
import collegeService from '../services/collegeService'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

const users = ref({ data: [] })
const roles = ref([])
const collegesList = ref([])

const modalRef = ref(null)
let modalInstance = null

const isEdit = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '',
  college_id: ''
})

const errors = reactive({})

/* RBAC */
const canCreate = computed(() =>
  ['super_admin','college_admin'].includes(auth.role)
)

const canDelete = computed(() =>
  auth.role === 'super_admin'
)

/* FETCH USERS */
const fetch = async (page = 1) => {
  const response = await userService.get(page)
  users.value = response.data
}

/* FETCH ROLES */
const fetchRoles = async () => {
  const response = await api.get('/roles')
  roles.value = response.data
}

/* FETCH COLLEGES */
const fetchColleges = async () => {
  const response = await collegeService.get()
  collegesList.value = response.data.data
}

/* MODAL */
const openCreate = () => {
  resetForm()
  isEdit.value = false
  modalInstance.show()
}

const openEdit = (user) => {
  resetForm()
  isEdit.value = true
  editingId.value = user.id

  form.name = user.name
  form.email = user.email
  form.role_id = user.role_id
  form.college_id = user.college_id

  modalInstance.show()
}

const closeModal = () => {
  modalInstance.hide()
}

/* SUBMIT */
const submit = async () => {
  clearErrors()

  try {
    if (isEdit.value) {
      await userService.update(editingId.value, form)
    } else {
      await userService.store(form)
    }

    closeModal()
    fetch()
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    }
  }
}

/* DELETE */
const remove = async (id) => {
  if (!confirm('Delete this user?')) return
  await userService.delete(id)
  fetch()
}

/* HELPERS */
const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  form.role_id = ''
  form.college_id = ''
  editingId.value = null
  clearErrors()
}

const clearErrors = () => {
  Object.keys(errors).forEach(key => delete errors[key])
}

/* LIFECYCLE */
onMounted(() => {
  modalInstance = new Modal(modalRef.value)
  fetch()
  fetchRoles()
  fetchColleges()
})
</script>