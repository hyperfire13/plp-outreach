<template>
  <MainLayout>
    <div class="card">

      <!-- HEADER -->
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Community Management</h4>

        <button
          v-if="canManage"
          class="btn btn-primary btn-sm"
          @click="openCreate"
        >
          <i class="fas fa-plus me-1"></i> Add Community
        </button>
      </div>

      <div class="card-body">

        <!-- FILTER SECTION -->
        <div class="row mb-3">

          <div class="col-md-3">
            <input
              v-model="filters.search"
              class="form-control"
              placeholder="Search by name..."
              @keyup.enter="fetch()"
            />
          </div>

          <div class="col-md-2">
            <select v-model="filters.urban_rural" class="form-control">
              <option value="">All Types</option>
              <option value="urban">Urban</option>
              <option value="rural">Rural</option>
            </select>
          </div>

          <div class="col-md-2">
            <select v-model="filters.disaster_risk_level" class="form-control">
              <option value="">All Risk Levels</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <div class="col-md-2">
            <button class="btn btn-secondary" @click="fetch()">
              Filter
            </button>
          </div>

        </div>

        <!-- TABLE -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Region</th>
                <th>Population</th>
                <th>Poverty %</th>
                <th>Urban/Rural</th>
                <th>Risk Level</th>
                <th width="170">Actions</th>
              </tr>
            </thead>
            <tbody>

              <tr v-for="community in communities.data" :key="community.id">
                <td>{{ community.name }}</td>
                <td>{{ community.region }}</td>
                <td>{{ community.population }}</td>
                <td>{{ community.poverty_rate }}</td>
                <td>{{ community.urban_rural }}</td>
                <td>
                  <span
                    :class="riskBadgeClass(community.disaster_risk_level)"
                    class="badge"
                  >
                    {{ community.disaster_risk_level }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-warning me-1"
                    @click="openEdit(community)"
                  >
                    Edit
                  </button>

                  <button
                    v-if="canManage"
                    class="btn btn-sm btn-danger"
                    @click="remove(community.id)"
                  >
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="!communities.data || communities.data.length === 0">
                <td colspan="7" class="text-center text-muted">
                  No communities found.
                </td>
              </tr>

            </tbody>
          </table>
        </div>

        <!-- PAGINATION -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div>
            Page {{ communities.current_page || 1 }}
            of {{ communities.last_page || 1 }}
          </div>

          <div>
            <button
              class="btn btn-sm btn-secondary me-2"
              :disabled="!communities.prev_page_url"
              @click="fetch(communities.current_page - 1)"
            >
              Previous
            </button>

            <button
              class="btn btn-sm btn-secondary"
              :disabled="!communities.next_page_url"
              @click="fetch(communities.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- CREATE / EDIT MODAL -->
    <div class="modal fade" tabindex="-1" ref="modalRef">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEdit ? 'Edit Community' : 'Create Community' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <div class="modal-body">
            <div class="row">

              <div class="col-md-6 mb-3">
                <label>Name</label>
                <input v-model="form.name" class="form-control" />
                <small class="text-danger">{{ errors.name }}</small>
              </div>

              <div class="col-md-6 mb-3">
                <label>Region</label>
                <input v-model="form.region" class="form-control" />
              </div>

              <div class="col-md-4 mb-3">
                <label>Population</label>
                <input v-model="form.population" type="number" class="form-control" />
              </div>

              <div class="col-md-4 mb-3">
                <label>Poverty Rate (%)</label>
                <input v-model="form.poverty_rate" type="number" step="0.01" class="form-control" />
              </div>

              <div class="col-md-4 mb-3">
                <label>Unemployment Rate (%)</label>
                <input v-model="form.unemployment_rate" type="number" step="0.01" class="form-control" />
              </div>

              <div class="col-md-4 mb-3">
                <label>Literacy Rate (%)</label>
                <input v-model="form.literacy_rate" type="number" step="0.01" class="form-control" />
              </div>

              <div class="col-md-4 mb-3">
                <label>Average Income</label>
                <input v-model="form.avg_income" type="number" step="0.01" class="form-control" />
              </div>

              <div class="col-md-4 mb-3">
                <label>Urban/Rural</label>
                <select v-model="form.urban_rural" class="form-control">
                  <option value="">Select</option>
                  <option value="urban">Urban</option>
                  <option value="rural">Rural</option>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label>Disaster Risk Level</label>
                <select v-model="form.disaster_risk_level" class="form-control">
                  <option value="">Select</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>

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
  import { ref, reactive, onMounted, computed } from 'vue'
  import { Modal } from 'bootstrap'
  import MainLayout from '../components/layout/MainLayout.vue'
  import communityService from '../services/communityService'
  import { useAuthStore } from '../stores/auth'

  const auth = useAuthStore()

  const communities = ref({ data: [] })
  const modalRef = ref(null)
  let modalInstance = null

  const isEdit = ref(false)
  const editingId = ref(null)

  const filters = reactive({
  search: '',
  urban_rural: '',
  disaster_risk_level: ''
  })

  const form = reactive({
  name: '',
  region: '',
  population: '',
  poverty_rate: '',
  unemployment_rate: '',
  literacy_rate: '',
  avg_income: '',
  urban_rural: '',
  disaster_risk_level: ''
  })

  const errors = reactive({})

  const canManage = computed(() =>
  ['super_admin','college_admin'].includes(auth.role)
  )

  const fetch = async (page = 1) => {
  const response = await communityService.get(page, filters)
  communities.value = response.data
  }

  const openCreate = () => {
  resetForm()
  isEdit.value = false
  modalInstance.show()
  }

  const openEdit = (community) => {
  resetForm()
  isEdit.value = true
  editingId.value = community.id
  Object.assign(form, community)
  modalInstance.show()
  }

  const closeModal = () => modalInstance.hide()

  const submit = async () => {
  clearErrors()
  try {
      if (isEdit.value) {
      await communityService.update(editingId.value, form)
      } else {
      await communityService.store(form)
      }
      closeModal()
      fetch()
  } catch (error) {
      if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
      }
  }
  }

  const remove = async (id) => {
  if (!confirm('Delete this community?')) return
  await communityService.delete(id)
  fetch()
  }

  const resetForm = () => {
  Object.keys(form).forEach(key => form[key] = '')
  editingId.value = null
  clearErrors()
  }

  const clearErrors = () => {
  Object.keys(errors).forEach(key => delete errors[key])
  }

  const riskBadgeClass = (risk) => {
  if (risk === 'high') return 'bg-danger'
  if (risk === 'medium') return 'bg-warning'
  if (risk === 'low') return 'bg-success'
  return 'bg-secondary'
  }

  onMounted(() => {
  modalInstance = new Modal(modalRef.value)
  fetch()
  })
</script>
