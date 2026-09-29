<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import projectProposalService from "@/services/projectProposalService";
import { useAuthorization } from "@/composables/useAuthorization";
import { ROLE_GROUPS } from "@/constants/roles";
const { canAccessRoles } = useAuthorization();
const modal = ref(null),
    detailModal = ref(null),
    rows = ref([]),
    options = ref({
        communities: [],
        priority_needs: [],
        survey_responses: [],
        statuses: [],
    }),
    pagination = ref({ current_page: 1, last_page: 1 }),
    selected = ref(null),
    editingId = ref(null),
    loading = ref(false),
    saving = ref(false),
    failure = ref(""),
    notice = ref(""),
    errors = ref({});
const filters = reactive({ search: "", status: "", page: 1, per_page: 10 });
const documentType = ref("other"),
    documentFile = ref(null);
const blank = () => ({
    community_id: "",
    priority_need_id: "",
    title: "",
    rationale: "",
    objectives: "",
    beneficiaries: "",
    expected_outputs: "",
    expected_outcomes: "",
    sustainability_plan: "",
    risk_assessment: "",
    monitoring_indicators: "",
    sdg_alignment: "",
    development_plan_alignment: "",
    partner_involvement: "",
    proposed_budget: 0,
    resources: [],
    workplans: [],
});
const form = reactive(blank());
const canCreate = computed(() =>
    canAccessRoles(ROLE_GROUPS.PROPOSAL_APPLICANTS),
);
const selectedCommunityNeeds = computed(() =>
    options.value.priority_needs.filter(
        (need) => Number(need.community_id) === Number(form.community_id),
    ),
);
const selectedCommunityResponses = computed(() =>
    options.value.survey_responses.filter(
        (response) =>
            Number(response.community_id) === Number(form.community_id),
    ),
);
const label = (v) =>
    (v || "").replaceAll("_", " ").replace(/\b\w/g, (c) => c.toUpperCase());
const person = (p) =>
    p?.full_name ||
    [p?.first_name, p?.middle_name, p?.last_name].filter(Boolean).join(" ") ||
    "Unknown";
const firstError = (k) =>
    Array.isArray(errors.value[k]) ? errors.value[k][0] : errors.value[k];
const displayAnswer = (value) => {
    if (Array.isArray(value)) return value.join(", ");
    if (value === true) return "Yes";
    if (value === false) return "No";
    if (value === null || value === undefined || value === "") return "—";

    return String(value);
};
async function load(page = 1) {
    loading.value = true;
    filters.page = page;
    try {
        const [list, opts] = await Promise.all([
            projectProposalService.paginate(filters),
            projectProposalService.options(),
        ]);
        rows.value = list.data.data;
        pagination.value = list.data;
        options.value = opts.data;
    } catch (e) {
        failure.value =
            e.response?.data?.message || "Unable to load project proposals.";
    } finally {
        loading.value = false;
    }
}
function openCreate() {
    editingId.value = null;
    Object.assign(form, blank());
    errors.value = {};
    modal.value?.open();
}
async function openDetail(row) {
    try {
        selected.value = (await projectProposalService.find(row.id)).data;
        detailModal.value?.open();
    } catch (e) {
        failure.value = e.response?.data?.message || "Unable to open proposal.";
    }
}
async function openEdit() {
    editingId.value = selected.value.id;
    Object.assign(form, blank(), selected.value, {
        community_id: selected.value.community_id,
    });
    detailModal.value?.close();
    modal.value?.open();
}
async function save() {
    saving.value = true;
    errors.value = {};
    try {
        const payload = {
            ...form,
            priority_need_id: Number(form.priority_need_id),
            proposed_budget: Number(form.proposed_budget),
            resources: form.resources || [],
            workplans: form.workplans || [],
        };
        delete payload.community_id;
        const r = editingId.value
            ? await projectProposalService.update(editingId.value, payload)
            : await projectProposalService.store(payload);
        notice.value = r.message;
        modal.value?.close();
        await load(editingId.value ? pagination.value.current_page : 1);
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
        failure.value = e.response?.data?.message || "Unable to save proposal.";
    } finally {
        saving.value = false;
    }
}
async function act(action, ...args) {
    try {
        const r = await projectProposalService[action](
            selected.value.id,
            ...args,
        );
        notice.value = r.message;
        selected.value = r.data;
        await load(pagination.value.current_page);
    } catch (e) {
        failure.value =
            e.response?.data?.message || "Unable to complete action.";
    }
}
async function review(decision) {
    const remarks =
        decision === "approve"
            ? window.prompt("Optional remarks:", "")
            : window.prompt("Remarks are required:");
    if (remarks === null || (decision !== "approve" && !remarks.trim())) return;
    await act("review", decision, remarks || null);
}
async function downloadNtp() {
    const r = await projectProposalService.downloadNtp(selected.value.id);
    const url = URL.createObjectURL(r.data),
        a = document.createElement("a");
    a.href = url;
    a.download = `${selected.value.notice_to_proceed.ntp_number}.pdf`;
    a.click();
    URL.revokeObjectURL(url);
}
async function uploadDocument() {
    if (!documentFile.value) return;
    const data = new FormData();
    data.append("document_type", documentType.value);
    data.append("file", documentFile.value);
    await projectProposalService.uploadDocument(selected.value.id, data);
    selected.value = (
        await projectProposalService.find(selected.value.id)
    ).data;
    documentFile.value = null;
    notice.value = "Supporting document uploaded successfully.";
}
async function downloadDocument(document) {
    const r = await projectProposalService.downloadDocument(
            selected.value.id,
            document.id,
        ),
        url = URL.createObjectURL(r.data),
        a = document.createElement("a");
    a.href = url;
    a.download = document.original_name;
    a.click();
    URL.revokeObjectURL(url);
}
async function deleteDocument(document) {
    if (!confirm(`Delete ${document.original_name}?`)) return;
    await projectProposalService.deleteDocument(selected.value.id, document.id);
    selected.value = (
        await projectProposalService.find(selected.value.id)
    ).data;
}
watch(
    () => form.community_id,
    () => {
        const selectedNeed = options.value.priority_needs.find(
            (need) => Number(need.id) === Number(form.priority_need_id),
        );

        if (
            selectedNeed &&
            Number(selectedNeed.community_id) !== Number(form.community_id)
        ) {
            form.priority_need_id = "";
        }
    },
);
onMounted(() => load());
</script>
<template>
    <MainLayout
        ><section class="content">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h1 class="h3">Community Extension Applications</h1>
                        <p class="text-muted">
                            Need-based proposals and multi-level approval
                            workflow.
                        </p>
                    </div>
                    <button
                        v-if="canCreate"
                        class="btn btn-primary align-self-start"
                        @click="openCreate"
                    >
                        Add Proposal
                    </button>
                </div>
                <div v-if="notice" class="alert alert-success">
                    {{ notice }}
                </div>
                <div v-if="failure" class="alert alert-danger">
                    {{ failure }}
                </div>
                <div class="card mb-3">
                    <div class="card-body row g-2">
                        <div class="col-md-6">
                            <input
                                v-model.trim="filters.search"
                                class="form-control"
                                placeholder="Search title or proposal number"
                            />
                        </div>
                        <div class="col-md-3">
                            <select
                                v-model="filters.status"
                                class="form-select"
                            >
                                <option value="">All statuses</option>
                                <option
                                    v-for="s in options.statuses"
                                    :key="s"
                                    :value="s"
                                >
                                    {{ label(s) }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button
                                class="btn btn-primary w-100"
                                @click="load(1)"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Proposal</th>
                                    <th>Applicant</th>
                                    <th>Community Need</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in rows" :key="row.id">
                                    <td>
                                        <strong>{{ row.title }}</strong>
                                        <div class="small text-muted">
                                            {{ row.proposal_number }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ person(row.applicant) }}
                                        <div class="small text-muted">
                                            {{ row.college?.name }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ row.priority_need?.need }}
                                        <div class="small text-muted">
                                            {{ row.community?.name }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ label(row.current_step) || "—" }}
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary">{{
                                            label(row.status)
                                        }}</span>
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            @click="openDetail(row)"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!loading && !rows.length">
                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-5"
                                    >
                                        No proposals found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <CrudPagination
                            :current-page="pagination.current_page"
                            :last-page="pagination.last_page"
                            :prev="!!pagination.prev_page_url"
                            :next="!!pagination.next_page_url"
                            @change="load"
                        />
                    </div>
                </div>
            </div>
        </section>
        <CrudModal
            ref="modal"
            :title="editingId ? 'Edit Proposal' : 'New Project Proposal'"
            :form="form"
            :errors="errors"
            size="xl"
            ><div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Community *</label>
                    <select v-model="form.community_id" class="form-select">
                        <option value="">Select community</option>
                        <option
                            v-for="community in options.communities"
                            :key="community.id"
                            :value="community.id"
                        >
                            {{ community.name }}
                            <template v-if="community.city">
                                — {{ community.city }}
                            </template>
                        </option>
                    </select>
                    <div class="form-text">
                        Select a community to review its validated needs and
                        submitted survey findings.
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Validated Community Need *</label
                    ><select
                        v-model="form.priority_need_id"
                        class="form-select"
                        :disabled="!form.community_id"
                    >
                        <option value="">Select validated need</option>
                        <option
                            v-for="n in selectedCommunityNeeds"
                            :key="n.id"
                            :value="n.id"
                        >
                            {{ n.need }} — {{ n.community?.name }}
                        </option></select
                    ><small class="text-danger">{{
                        firstError("priority_need_id")
                    }}</small>
                    <div
                        v-if="
                            form.community_id && !selectedCommunityNeeds.length
                        "
                        class="form-text text-warning"
                    >
                        This community has no validated priority need yet, so a
                        proposal cannot be created for it.
                    </div>
                </div>
                <div class="col-12">
                    <section class="card community-insights-panel">
                        <div class="card-body p-0">
                            <header class="insights-header">
                                <span class="insights-header-icon">
                                    <i class="bi bi-lightbulb"></i>
                                </span>
                                <div class="col-md-12">
                                    <h5 class="card-title mb-1">
                                        Community Needs and Survey Insights
                                    </h5>
                                    &nbsp;
                                    <p class="small text-muted mb-0">
                                        Use these verified findings as guidance
                                        when preparing the proposal.
                                    </p>
                                </div>
                            </header>
                            <div v-if="!form.community_id" class="text-muted">
                                Select a community to display its information.
                            </div>
                            <div v-else class="community-insights">
                                <section class="needs-section">
                                    <h6 class="insights-section-title">
                                        <i class="bi bi-patch-check-fill"></i>
                                        Validated Priority Needs
                                    </h6>
                                    <ul
                                        v-if="selectedCommunityNeeds.length"
                                        class="needs-list"
                                    >
                                        <li
                                            v-for="need in selectedCommunityNeeds"
                                            :key="need.id"
                                            class="need-item"
                                        >
                                            <span class="need-number">
                                                {{ need.priority_rank || "•" }}
                                            </span>
                                            <div class="need-content">
                                                <div class="need-title-row">
                                                    <strong>{{
                                                        need.need
                                                    }}</strong>
                                                    <span
                                                        v-if="
                                                            need.priority_rank
                                                        "
                                                        class="priority-badge"
                                                    >
                                                        Priority
                                                        {{ need.priority_rank }}
                                                    </span>
                                                </div>
                                                <p
                                                    v-if="need.description"
                                                    class="need-description"
                                                >
                                                    {{ need.description }}
                                                </p>
                                            </div>
                                        </li>
                                    </ul>
                                    <p v-else class="text-muted mb-0">
                                        No validated priority needs found.
                                    </p>
                                </section>
                                <h6 class="insights-section-title mt-3">
                                    <i class="bi bi-clipboard-data-fill"></i>
                                    Submitted Survey Findings
                                </h6>
                                <article
                                    v-for="response in selectedCommunityResponses"
                                    :key="response.id"
                                    class="survey-finding"
                                >
                                    <div class="survey-meta">
                                        <i class="bi bi-file-earmark-text"></i>
                                        {{
                                            response.template?.title || "Survey"
                                        }}
                                        <span v-if="response.survey_date">
                                            · {{ response.survey_date }}
                                        </span>
                                    </div>
                                    <div class="survey-highlights">
                                        <div class="survey-highlight">
                                            <span class="highlight-icon">
                                                <i class="bi bi-stars"></i>
                                            </span>
                                            <div>
                                                <strong
                                                    >Suggested outreach
                                                    program</strong
                                                >
                                                <p>
                                                    {{
                                                        response.suggested_outreach_program ||
                                                        "No suggestion provided"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="survey-highlight">
                                            <span class="highlight-icon">
                                                <i
                                                    class="bi bi-chat-left-text"
                                                ></i>
                                            </span>
                                            <div>
                                                <strong>Remarks</strong>
                                                <p>
                                                    {{
                                                        response.remarks ||
                                                        "No remarks provided"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <dl class="answer-list mb-0">
                                        <template
                                            v-for="answer in response.answers"
                                            :key="answer.id"
                                        >
                                            <dt class="answer-question">
                                                <span
                                                    v-if="answer.section"
                                                    class="text-muted"
                                                >
                                                    {{ answer.section }} —
                                                </span>
                                                {{ answer.question }}
                                            </dt>
                                            <dd class="answer-value">
                                                {{
                                                    displayAnswer(answer.value)
                                                }}
                                            </dd>
                                        </template>
                                    </dl>
                                </article>
                                <p
                                    v-if="!selectedCommunityResponses.length"
                                    class="text-muted mb-0"
                                >
                                    No submitted survey responses found for this
                                    community.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-12">
                    <label class="form-label">Project Title *</label
                    ><input v-model.trim="form.title" class="form-control" />
                </div>
                <div
                    v-for="field in [
                        'rationale',
                        'objectives',
                        'beneficiaries',
                        'expected_outputs',
                        'expected_outcomes',
                        'sustainability_plan',
                        'risk_assessment',
                        'monitoring_indicators',
                        'sdg_alignment',
                        'development_plan_alignment',
                        'partner_involvement',
                    ]"
                    :key="field"
                    class="col-md-6"
                >
                    <label class="form-label"
                        >{{ label(field)
                        }}{{
                            field === "partner_involvement" ? "" : " *"
                        }}</label
                    ><textarea
                        v-model.trim="form[field]"
                        class="form-control"
                        rows="3"
                    ></textarea
                    ><small class="text-danger">{{ firstError(field) }}</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Proposed Budget *</label
                    ><input
                        v-model="form.proposed_budget"
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-control"
                    />
                </div>
            </div>
            <template #footer
                ><button class="btn btn-secondary" @click="modal?.close()">
                    Cancel</button
                ><button
                    class="btn btn-primary"
                    :disabled="saving"
                    @click="save"
                >
                    {{ saving ? "Saving..." : "Save Draft" }}
                </button></template
            ></CrudModal
        >
        <CrudModal
            ref="detailModal"
            title="Project Proposal"
            :form="{}"
            :errors="{}"
            size="xl"
            ><div v-if="selected">
                <h4>{{ selected.title }}</h4>
                <p class="text-muted">
                    {{ selected.proposal_number }} ·
                    {{ label(selected.status) }} ·
                    {{ label(selected.current_step) }}
                </p>
                <dl class="row">
                    <dt class="col-sm-3">Applicant</dt>
                    <dd class="col-sm-9">{{ person(selected.applicant) }}</dd>
                    <dt class="col-sm-3">Validated need</dt>
                    <dd class="col-sm-9">
                        {{ selected.priority_need?.need }} —
                        {{ selected.community?.name }}
                    </dd>
                    <dt class="col-sm-3">Rationale</dt>
                    <dd class="col-sm-9">{{ selected.rationale }}</dd>
                    <dt class="col-sm-3">Objectives</dt>
                    <dd class="col-sm-9">{{ selected.objectives }}</dd>
                    <dt class="col-sm-3">Budget</dt>
                    <dd class="col-sm-9">
                        ₱{{ Number(selected.proposed_budget).toLocaleString() }}
                    </dd>
                </dl>
                <h5>Supporting Documents</h5>
                <ul>
                    <li v-for="d in selected.documents" :key="d.id">
                        <button
                            class="btn btn-link p-0"
                            @click="downloadDocument(d)"
                        >
                            {{ d.original_name }}</button
                        ><button
                            v-if="selected.permissions?.update"
                            class="btn btn-sm text-danger"
                            @click="deleteDocument(d)"
                        >
                            Delete
                        </button>
                    </li>
                </ul>
                <div
                    v-if="selected.permissions?.update"
                    class="input-group mb-3"
                >
                    <select v-model="documentType" class="form-select">
                        <option
                            v-for="t in [
                                'moa',
                                'letter',
                                'budget',
                                'workplan',
                                'other',
                            ]"
                            :key="t"
                            :value="t"
                        >
                            {{ label(t) }}
                        </option></select
                    ><input
                        type="file"
                        class="form-control"
                        accept=".pdf,.doc,.docx,.xls,.xlsx"
                        @change="documentFile = $event.target.files[0]"
                    /><button
                        class="btn btn-outline-primary"
                        @click="uploadDocument"
                    >
                        Upload
                    </button>
                </div>
                <h5>Approval History</h5>
                <ul>
                    <li v-for="a in selected.approvals" :key="a.id">
                        {{ label(a.step) }} — {{ label(a.decision) }} by
                        {{ person(a.actor) }}
                        <span v-if="a.remarks">({{ a.remarks }})</span>
                    </li>
                </ul>
            </div>
            <template #footer
                ><button
                    class="btn btn-secondary"
                    @click="detailModal?.close()"
                >
                    Close</button
                ><button
                    v-if="selected?.permissions?.update"
                    class="btn btn-outline-primary"
                    @click="openEdit"
                >
                    Edit</button
                ><button
                    v-if="selected?.permissions?.submit"
                    class="btn btn-primary"
                    @click="act('submit')"
                >
                    Submit</button
                ><button
                    v-if="selected?.permissions?.review"
                    class="btn btn-success"
                    @click="review('approve')"
                >
                    Approve / Endorse</button
                ><button
                    v-if="selected?.permissions?.review"
                    class="btn btn-warning"
                    @click="review('revision')"
                >
                    Request Revision</button
                ><button
                    v-if="selected?.permissions?.review"
                    class="btn btn-danger"
                    @click="review('reject')"
                >
                    Reject</button
                ><button
                    v-if="selected?.permissions?.issue_ntp"
                    class="btn btn-success"
                    @click="act('issueNtp')"
                >
                    Issue NTP</button
                ><button
                    v-if="selected?.notice_to_proceed"
                    class="btn btn-outline-success"
                    @click="downloadNtp"
                >
                    Download NTP
                </button></template
            ></CrudModal
        >
    </MainLayout>
</template>

<style scoped>
.community-insights-panel {
    overflow: hidden;
    background: #f7fcf9;
    border: 1px solid #cfe8dc;
    box-shadow: 0 8px 22px rgba(8, 107, 82, 0.08);
}

.insights-header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem 1.15rem;
    background: linear-gradient(110deg, #e5f7ee, #f8fcfa);
    border-bottom: 1px solid #cfe8dc;
}

.insights-header-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    color: #fff;
    background: linear-gradient(145deg, #31b975, #087258);
    border-radius: 12px;
    box-shadow: 0 6px 13px rgba(8, 114, 88, 0.2);
}

.insights-header-icon i {
    font-size: 1.15rem;
}

.insights-header .card-title {
    color: #075b47;
    font-size: 1rem;
}

.community-insights-panel > .card-body > .text-muted {
    padding: 1.25rem;
}

.community-insights {
    max-height: 28rem;
    overflow-y: auto;
    padding: 1rem 1.1rem 1.1rem;
    scrollbar-color: #72b99e #e5f0ec;
    scrollbar-width: thin;
}

.community-insights::-webkit-scrollbar {
    width: 8px;
}

.community-insights::-webkit-scrollbar-track {
    background: #e5f0ec;
    border-radius: 10px;
}

.community-insights::-webkit-scrollbar-thumb {
    background: #72b99e;
    border-radius: 10px;
}

.needs-section {
    padding-bottom: 0.25rem;
}

.insights-section-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.7rem;
    color: #164c3e;
    font-size: 0.84rem;
    font-weight: 750;
    letter-spacing: 0.015em;
    text-transform: uppercase;
}

.insights-section-title i {
    color: #159665;
}

.needs-list {
    display: grid;
    gap: 0.55rem;
    padding: 0;
    margin: 0;
    list-style: none;
}

.need-item {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    padding: 0.75rem 0.85rem;
    background: #fff;
    border: 1px solid #dcece5;
    border-left: 4px solid #1ca56c;
    border-radius: 10px;
}

.need-number {
    display: grid;
    width: 27px;
    height: 27px;
    flex: 0 0 27px;
    place-items: center;
    color: #087258;
    background: #dff5e9;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 750;
}

.need-content {
    min-width: 0;
    flex: 1;
}

.need-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    color: #183b32;
}

.priority-badge {
    flex: 0 0 auto;
    padding: 0.22rem 0.55rem;
    color: #087258;
    background: #e2f6ed;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
}

.need-description {
    margin: 0.25rem 0 0;
    color: #667b74;
    font-size: 0.76rem;
    line-height: 1.45;
}

.survey-finding {
    overflow: hidden;
    margin-bottom: 0.85rem;
    background: #fff;
    border: 1px solid #d7e8e1;
    border-radius: 11px;
}

.survey-meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 0.85rem;
    color: #527065;
    background: #f0f8f4;
    border-bottom: 1px solid #dcece5;
    font-size: 0.72rem;
    font-weight: 600;
}

.survey-meta i {
    color: #159665;
}

.survey-highlights {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem;
    padding: 0.8rem;
}

.survey-highlight {
    display: flex;
    gap: 0.6rem;
    padding: 0.7rem;
    color: #21473b;
    background: #f3faf7;
    border: 1px solid #dceee6;
    border-radius: 9px;
}

.highlight-icon {
    display: grid;
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    place-items: center;
    color: #087258;
    background: #dff5e9;
    border-radius: 8px;
}

.survey-highlight strong {
    display: block;
    margin-bottom: 0.15rem;
    color: #087258;
    font-size: 0.72rem;
}

.survey-highlight p {
    margin: 0;
    color: #425f56;
    font-size: 0.78rem;
    line-height: 1.4;
}

.answer-list {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    padding: 0 0.8rem 0.8rem;
}

.answer-question,
.answer-value {
    min-width: 0;
    padding: 0.55rem 0.65rem;
    margin: 0;
    border-top: 1px solid #e8f0ed;
    font-size: 0.75rem;
    line-height: 1.4;
}

.answer-question {
    color: #405b52;
    background: #fbfdfc;
    font-weight: 600;
}

.answer-value {
    color: #153d32;
    background: #fff;
}

@media (max-width: 767.98px) {
    .survey-highlights,
    .answer-list {
        grid-template-columns: 1fr;
    }

    .answer-value {
        padding-top: 0.1rem;
        border-top: 0;
    }

    .need-title-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.3rem;
    }
}
</style>
