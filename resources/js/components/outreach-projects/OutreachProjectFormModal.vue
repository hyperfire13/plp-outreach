<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from "vue";
import { Modal } from "bootstrap";
import ProjectStatusBadge from "./ProjectStatusBadge.vue";

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },

    programs: {
        type: Array,
        default: () => [],
    },

    colleges: {
        type: Array,
        default: () => [],
    },

    coordinators: {
        type: Array,
        default: () => [],
    },

    errors: {
        type: Object,
        default: () => ({}),
    },

    generalError: {
        type: String,
        default: "",
    },

    submitting: {
        type: Boolean,
        default: false,
    },

    editing: {
        type: Boolean,
        default: false,
    },

    canAssignCollege: {
        type: Boolean,
        default: false,
    },

    canAssignCoordinator: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "update:modelValue",
    "submit",
    "closed",
]);

const modalElement = ref(null);
let modalInstance = null;

const form = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const title = computed(() =>
    props.editing
        ? "Edit Outreach Project"
        : "Create Outreach Project",
);

const getError = (field) => {
    return props.errors?.[field]?.[0] ?? "";
};

const open = async () => {
    await nextTick();

    modalInstance ??= Modal.getOrCreateInstance(
        modalElement.value,
        {
            backdrop: "static",
            keyboard: false,
        },
    );

    modalInstance.show();
};

const close = () => {
    modalInstance?.hide();
};

const handleHidden = () => {
    emit("closed");
};

onBeforeUnmount(() => {
    modalInstance?.dispose();
    modalInstance = null;
});

defineExpose({
    open,
    close,
});
</script>

<template>
    <div
        ref="modalElement"
        class="modal fade"
        tabindex="-1"
        aria-hidden="true"
        @hidden.bs.modal="handleHidden"
    >
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form
                class="modal-content"
                @submit.prevent="emit('submit')"
            >
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">
                            {{ title }}
                        </h5>

                        <ProjectStatusBadge
                            v-if="editing && form.status"
                            :status="form.status"
                        />
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        :disabled="submitting"
                        @click="close"
                    />
                </div>

                <div class="modal-body">
                    <div
                        v-if="generalError"
                        class="alert alert-danger"
                    >
                        {{ generalError }}
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Program template
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                v-model="form.outreach_program_id"
                                class="form-select"
                                :class="{
                                    'is-invalid':
                                        getError(
                                            'outreach_program_id',
                                        ),
                                }"
                                :disabled="submitting"
                            >
                                <option value="">
                                    Select program
                                </option>

                                <option
                                    v-for="program in programs"
                                    :key="program.id"
                                    :value="program.id"
                                >
                                    {{ program.name }}
                                    <template v-if="program.category">
                                        — {{ program.category }}
                                    </template>
                                </option>
                            </select>

                            <div class="invalid-feedback">
                                {{
                                    getError(
                                        "outreach_program_id",
                                    )
                                }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                College
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                v-model="form.college_id"
                                class="form-select"
                                :class="{
                                    'is-invalid':
                                        getError('college_id'),
                                }"
                                :disabled="
                                    submitting ||
                                    !canAssignCollege
                                "
                            >
                                <option value="">
                                    Select college
                                </option>

                                <option
                                    v-for="college in colleges"
                                    :key="college.id"
                                    :value="college.id"
                                >
                                    {{ college.name }}
                                </option>
                            </select>

                            <div class="invalid-feedback">
                                {{ getError("college_id") }}
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Project title
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                v-model.trim="form.title"
                                type="text"
                                class="form-control"
                                :class="{
                                    'is-invalid':
                                        getError('title'),
                                }"
                                maxlength="255"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{ getError("title") }}
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Description
                            </label>

                            <textarea
                                v-model.trim="form.description"
                                class="form-control"
                                rows="3"
                                :class="{
                                    'is-invalid':
                                        getError('description'),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{ getError("description") }}
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Objectives
                            </label>

                            <textarea
                                v-model.trim="form.objectives"
                                class="form-control"
                                rows="3"
                                :class="{
                                    'is-invalid':
                                        getError('objectives'),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{ getError("objectives") }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Location
                            </label>

                            <input
                                v-model.trim="form.location"
                                type="text"
                                class="form-control"
                                :class="{
                                    'is-invalid':
                                        getError('location'),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{ getError("location") }}
                            </div>
                        </div>

                        <div
                            v-if="canAssignCoordinator"
                            class="col-md-6"
                        >
                            <label class="form-label">
                                Coordinator
                            </label>

                            <select
                                v-model="form.coordinator_id"
                                class="form-select"
                                :class="{
                                    'is-invalid':
                                        getError(
                                            'coordinator_id',
                                        ),
                                }"
                                :disabled="submitting"
                            >
                                <option value="">
                                    No coordinator assigned
                                </option>

                                <option
                                    v-for="coordinator in coordinators"
                                    :key="coordinator.id"
                                    :value="coordinator.id"
                                >
                                    {{
                                        coordinator.full_name ??
                                        [
                                            coordinator.first_name,
                                            coordinator.middle_name,
                                            coordinator.last_name,
                                        ]
                                            .filter(Boolean)
                                            .join(" ")
                                    }}
                                </option>
                            </select>

                            <div class="invalid-feedback">
                                {{
                                    getError(
                                        "coordinator_id",
                                    )
                                }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Start date
                            </label>

                            <input
                                v-model="form.start_date"
                                type="date"
                                class="form-control"
                                :class="{
                                    'is-invalid':
                                        getError('start_date'),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{ getError("start_date") }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                End date
                            </label>

                            <input
                                v-model="form.end_date"
                                type="date"
                                class="form-control"
                                :min="form.start_date || undefined"
                                :class="{
                                    'is-invalid':
                                        getError('end_date'),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{ getError("end_date") }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Proposed budget
                            </label>

                            <input
                                v-model="form.proposed_budget"
                                type="number"
                                class="form-control"
                                min="0"
                                step="0.01"
                                :class="{
                                    'is-invalid':
                                        getError(
                                            'proposed_budget',
                                        ),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{
                                    getError(
                                        "proposed_budget",
                                    )
                                }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Expected beneficiaries
                            </label>

                            <input
                                v-model="form.expected_beneficiaries"
                                type="number"
                                class="form-control"
                                min="0"
                                step="1"
                                :class="{
                                    'is-invalid':
                                        getError(
                                            'expected_beneficiaries',
                                        ),
                                }"
                                :disabled="submitting"
                            />

                            <div class="invalid-feedback">
                                {{
                                    getError(
                                        'expected_beneficiaries',
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        :disabled="submitting"
                        @click="close"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="submitting"
                    >
                        <span
                            v-if="submitting"
                            class="spinner-border spinner-border-sm me-2"
                        />

                        {{ editing ? "Save changes" : "Create project" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
