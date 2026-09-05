<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudPage from "@/components/crud/CrudPage.vue";
import CrudTable from "@/components/crud/CrudTable.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import CrudModal from "@/components/crud/CrudModal.vue";
import outreachRecordService from "@/services/outreachRecordService";
import { useApiErrors } from "@/composables/useApiErrors";
import { useAuthStore } from "@/stores/auth";
import { ROLE_GROUPS } from "@/constants/roles";

const authStore = useAuthStore();
const { errors, generalError, clearErrors, captureError } = useApiErrors();
const records = ref({ data: [], current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null });
const options = ref({ communities: [], programs: [], colleges: [] });
const modalRef = ref(null);
const loading = ref(false);
const saving = ref(false);
const editingId = ref(null);
const filters = reactive({ search: "", year: "", college_id: "" });
const form = reactive({ community_id: "", outreach_program_id: "", college_id: "", execution_date: "", budget_used: "", volunteers_count: 0, impact_score: "", success_rate: "", satisfaction_rating: "" });

const canCreate = computed(() => ROLE_GROUPS.OUTREACH_RECORD_ENCODERS.includes(authStore.role));
const isAdministrator = computed(() => ["super_admin", "calo_administrator"].includes(authStore.role));
const isCollegeAdmin = computed(() => authStore.role === "college_admin");
const fields = computed(() => [
    { key: "community_id", label: "Community", type: "select", required: true, options: options.value.communities.map(item => ({ value: item.id, label: `${item.name}${item.city ? ` - ${item.city}` : ""}` })) },
    { key: "outreach_program_id", label: "Outreach Program", type: "select", required: true, options: options.value.programs.map(item => ({ value: item.id, label: item.name })) },
    { key: "college_id", label: "College", type: "select", required: true, options: options.value.colleges.map(item => ({ value: item.id, label: item.name })) },
    { key: "execution_date", label: "Execution Date", type: "date", required: true },
    { key: "budget_used", label: "Budget Used", type: "number" },
    { key: "volunteers_count", label: "Volunteer Count", type: "number" },
    { key: "impact_score", label: "Impact Score (0-100)", type: "number" },
    { key: "success_rate", label: "Success Rate (0-100)", type: "number" },
    { key: "satisfaction_rating", label: "Satisfaction Rating (0-5)", type: "number" },
]);
const columns = [
    { key: "program_name", label: "Program" }, { key: "community_name", label: "Community" },
    { key: "college_name", label: "College" }, { key: "execution_date", label: "Execution Date" },
    { key: "budget_display", label: "Budget Used" }, { key: "volunteers_count", label: "Volunteers" },
    { key: "impact_display", label: "Impact / Success" }, { key: "satisfaction_display", label: "Satisfaction" },
];
const rows = computed(() => records.value.data.map(record => ({
    ...record,
    program_name: record.outreach_program?.name || "-",
    community_name: record.community?.name || "-",
    college_name: record.college?.name || "-",
    execution_date: String(record.execution_date || "").slice(0, 10),
    budget_display: record.budget_used == null ? "-" : new Intl.NumberFormat("en-PH", { style: "currency", currency: "PHP" }).format(record.budget_used),
    impact_display: `${record.impact_score ?? "-"} / ${record.success_rate ?? "-"}%`,
    satisfaction_display: record.satisfaction_rating == null ? "-" : `${record.satisfaction_rating} / 5`,
})));

function canManage(record) {
    return isAdministrator.value || Number(record.created_by) === Number(authStore.userId) || (isCollegeAdmin.value && Number(record.college_id) === Number(authStore.user?.college_id));
}
async function fetchRecords(page = 1) {
    loading.value = true; generalError.value = "";
    try { records.value = (await outreachRecordService.paginate({ ...filters, page })).data; }
    catch (error) { captureError(error); }
    finally { loading.value = false; }
}
async function loadOptions() {
    try {
        options.value = (await outreachRecordService.options()).data;
        if (options.value.colleges.length === 1) form.college_id = options.value.colleges[0].id;
    } catch (error) { captureError(error); }
}
function resetForm() {
    Object.assign(form, { community_id: "", outreach_program_id: "", college_id: options.value.colleges.length === 1 ? options.value.colleges[0].id : "", execution_date: "", budget_used: "", volunteers_count: 0, impact_score: "", success_rate: "", satisfaction_rating: "" });
    editingId.value = null; clearErrors();
}
function openCreate() { resetForm(); modalRef.value.open(); }
function openEdit(record) {
    resetForm(); editingId.value = record.id;
    Object.keys(form).forEach(key => { form[key] = record[key] ?? ""; });
    form.execution_date = String(record.execution_date || "").slice(0, 10);
    modalRef.value.open();
}
function payload() {
    const nullableNumbers = ["budget_used", "impact_score", "success_rate", "satisfaction_rating"];
    const data = { ...form, volunteers_count: Number(form.volunteers_count || 0) };
    nullableNumbers.forEach(key => { data[key] = data[key] === "" ? null : Number(data[key]); });
    data.community_id = Number(data.community_id); data.outreach_program_id = Number(data.outreach_program_id); data.college_id = Number(data.college_id);
    return data;
}
async function submit() {
    if (saving.value) return; saving.value = true; clearErrors();
    try {
        editingId.value ? await outreachRecordService.update(editingId.value, payload()) : await outreachRecordService.store(payload());
        modalRef.value.close(); await fetchRecords(records.value.current_page || 1);
    } catch (error) { captureError(error); }
    finally { saving.value = false; }
}
async function removeRecord(record) {
    if (!confirm(`Delete the accomplishment record for ${record.program_name}?`)) return;
    try { await outreachRecordService.remove(record.id); await fetchRecords(records.value.data.length === 1 && records.value.current_page > 1 ? records.value.current_page - 1 : records.value.current_page); }
    catch (error) { captureError(error); }
}
onMounted(async () => { await Promise.all([loadOptions(), fetchRecords()]); });
</script>

<template>
  <MainLayout><section class="content"><div class="container-fluid py-3">
    <div v-if="generalError" class="alert alert-danger">{{ generalError }}</div>
    <CrudPage title="Accomplishment Records" subtitle="Document completed outreach results and measurable outcomes." add-label="Add Record" :can-create="canCreate" :loading="loading" @create="openCreate">
      <template #filters><div class="row g-2">
        <div class="col-md-5"><input v-model.trim="filters.search" class="form-control" placeholder="Search program, community, or college"></div>
        <div class="col-md-2"><input v-model="filters.year" type="number" min="1900" max="2100" class="form-control" placeholder="Year"></div>
        <div class="col-md-3"><select v-model="filters.college_id" class="form-select"><option value="">All colleges</option><option v-for="college in options.colleges" :key="college.id" :value="college.id">{{ college.name }}</option></select></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100" :disabled="loading" @click="fetchRecords(1)">Apply</button></div>
      </div></template>
      <CrudTable :columns="columns" :rows="rows" :loading="loading" :actions="canCreate" empty-message="No accomplishment records found.">
        <template #actions="{ row }"><template v-if="canManage(row)"><button class="btn btn-sm btn-outline-primary me-1" @click="openEdit(row)"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" @click="removeRecord(row)"><i class="bi bi-trash"></i></button></template></template>
      </CrudTable>
      <CrudPagination :current-page="records.current_page || 1" :last-page="records.last_page || 1" :prev="Boolean(records.prev_page_url)" :next="Boolean(records.next_page_url)" @change="fetchRecords" />
    </CrudPage>
    <CrudModal ref="modalRef" :title="editingId ? 'Edit Accomplishment Record' : 'Add Accomplishment Record'" :fields="fields" :form="form" :errors="errors" :submit-label="saving ? 'Saving...' : editingId ? 'Update' : 'Save'" @submit="submit" />
  </div></section></MainLayout>
</template>
