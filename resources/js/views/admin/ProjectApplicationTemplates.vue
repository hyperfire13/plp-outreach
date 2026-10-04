<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import CrudModal from "@/components/crud/CrudModal.vue";
import CrudPagination from "@/components/crud/CrudPagination.vue";
import projectApplicationTemplateService from "@/services/projectApplicationTemplateService";

const rows = ref([]);
const pagination = ref({ current_page: 1, last_page: 1 });
const modal = ref(null);
const editingId = ref(null);
const loading = ref(false);
const saving = ref(false);
const failure = ref("");
const notice = ref("");
const errors = ref({});
const filters = reactive({
    search: "",
    status: "",
    phase: "",
    page: 1,
    per_page: 10,
});

const fieldTypes = [
    { value: "text", label: "Short Text" },
    { value: "textarea", label: "Long Text" },
    { value: "email", label: "Email" },
    { value: "number", label: "Number" },
    { value: "date", label: "Date" },
    { value: "time", label: "Time" },
    { value: "select", label: "Dropdown" },
    { value: "radio", label: "Single Choice" },
    { value: "checkbox", label: "Yes / No Checkbox" },
    { value: "checkbox_group", label: "Multiple Choice" },
];
const optionBasedTypes = ["select", "radio", "checkbox_group"];

const newField = () => ({
    key: "",
    label: "",
    type: "text",
    required: false,
    options: [],
    help_text: "",
    _optionInput: "",
});

const newSection = (number = 1) => ({
    title: `Section ${number}`,
    fields: [newField()],
});

const blank = () => ({
    code: "",
    title: "",
    description: "",
    version: 1,
    phase: "application",
    status: "draft",
    is_required: false,
    sort_order: 0,
    schema: { sections: [newSection()] },
});

const form = reactive(blank());
const isEditing = computed(() => editingId.value !== null);
const firstError = (key) => {
    const error = errors.value[key];

    return Array.isArray(error) ? error[0] : error;
};
const label = (value = "") =>
    value
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
const usesOptions = (field) => optionBasedTypes.includes(field.type);
const builderError = (sectionIndex, fieldIndex = null, property = null) => {
    let key = `schema.sections.${sectionIndex}`;

    if (fieldIndex !== null) key += `.fields.${fieldIndex}`;
    if (property) key += `.${property}`;

    return firstError(key);
};

function hydrateSchema(schema) {
    return {
        sections: (schema?.sections || []).map((section) => ({
            title: section.title || "",
            fields: (section.fields || []).map((field) => ({
                ...field,
                options: Array.isArray(field.options) ? [...field.options] : [],
                help_text: field.help_text || "",
                required: Boolean(field.required),
                _optionInput: "",
            })),
        })),
    };
}

function cleanSchema() {
    return {
        sections: form.schema.sections.map((section) => ({
            title: section.title.trim(),
            fields: section.fields.map((field) => ({
                key: field.key.trim(),
                label: field.label.trim(),
                type: field.type,
                required: Boolean(field.required),
                ...(usesOptions(field) ? { options: [...field.options] } : {}),
                ...(field.help_text?.trim()
                    ? { help_text: field.help_text.trim() }
                    : {}),
            })),
        })),
    };
}

function generateKey(field) {
    if (field.key || !field.label) return;

    field.key = field.label
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "")
        .replace(/^[^a-z]+/, "field_")
        .slice(0, 80);
}

function addSection() {
    form.schema.sections.push(newSection(form.schema.sections.length + 1));
}

function removeSection(index) {
    if (form.schema.sections.length > 1) form.schema.sections.splice(index, 1);
}

function moveItem(items, index, direction) {
    const target = index + direction;

    if (target < 0 || target >= items.length) return;

    [items[index], items[target]] = [items[target], items[index]];
}

function addField(section) {
    section.fields.push(newField());
}

function removeField(section, index) {
    if (section.fields.length > 1) section.fields.splice(index, 1);
}

function changeFieldType(field) {
    if (!usesOptions(field)) {
        field.options = [];
        field._optionInput = "";
    }
}

function addOption(field) {
    const option = field._optionInput.trim();

    if (!option || field.options.includes(option)) return;

    field.options.push(option);
    field._optionInput = "";
}

function removeOption(field, index) {
    field.options.splice(index, 1);
}

async function load(page = 1) {
    loading.value = true;
    failure.value = "";
    filters.page = page;

    try {
        const response =
            await projectApplicationTemplateService.paginate(filters);
        rows.value = response.data.data;
        pagination.value = response.data;
    } catch (error) {
        failure.value =
            error.response?.data?.message || "Unable to load templates.";
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    Object.assign(form, blank());
    editingId.value = null;
    errors.value = {};
}

function openCreate() {
    resetForm();
    modal.value?.open();
}

function openEdit(template) {
    resetForm();
    editingId.value = template.id;
    Object.assign(form, {
        code: template.code,
        title: template.title,
        description: template.description || "",
        version: template.version,
        phase: template.phase,
        status: template.status,
        is_required: Boolean(template.is_required),
        sort_order: template.sort_order,
        schema: hydrateSchema(template.schema),
    });
    modal.value?.open();
}

async function save() {
    saving.value = true;
    errors.value = {};
    failure.value = "";

    try {
        const payload = {
            code: form.code,
            title: form.title,
            description: form.description || null,
            version: Number(form.version),
            phase: form.phase,
            status: form.status,
            is_required: Boolean(form.is_required),
            sort_order: Number(form.sort_order),
            schema: cleanSchema(),
        };
        const response = isEditing.value
            ? await projectApplicationTemplateService.update(
                  editingId.value,
                  payload,
              )
            : await projectApplicationTemplateService.store(payload);

        notice.value = response.message;
        modal.value?.close();
        await load(pagination.value.current_page || 1);
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
        failure.value =
            error.response?.data?.message || "Unable to save the template.";
    } finally {
        saving.value = false;
    }
}

async function remove(template) {
    if (!window.confirm(`Delete template "${template.title}"?`)) return;

    try {
        const response = await projectApplicationTemplateService.remove(
            template.id,
        );
        notice.value = response.message;
        await load(pagination.value.current_page || 1);
    } catch (error) {
        failure.value =
            error.response?.data?.message || "Unable to delete the template.";
    }
}

onMounted(load);
</script>

<template>
    <MainLayout>
        <section class="content">
            <div class="container-fluid py-3">
                <div
                    class="d-flex justify-content-between align-items-start gap-3 mb-3"
                >
                    <div>
                        <h1 class="h3 mb-1">Project Application Templates</h1>
                        <p class="text-muted mb-0">
                            Manage versioned forms used throughout the project
                            application and evaluation lifecycle.
                        </p>
                    </div>
                    <button class="btn btn-primary" @click="openCreate">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add Template
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
                        <div class="col-lg-6">
                            <input
                                v-model.trim="filters.search"
                                class="form-control"
                                placeholder="Search title or code"
                                @keyup.enter="load(1)"
                            />
                        </div>
                        <div class="col-lg-2">
                            <select v-model="filters.phase" class="form-select">
                                <option value="">All phases</option>
                                <option value="application">Application</option>
                                <option value="implementation">
                                    Implementation
                                </option>
                                <option value="monitoring">Monitoring</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <select
                                v-model="filters.status"
                                class="form-select"
                            >
                                <option value="">All statuses</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
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
                                    <th>Template</th>
                                    <th>Phase</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Responses</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in rows" :key="row.id">
                                    <td>
                                        <div class="fw-semibold">
                                            {{ row.title }}
                                        </div>
                                        <small class="text-muted">
                                            {{ row.code }}
                                        </small>
                                    </td>
                                    <td>{{ label(row.phase) }}</td>
                                    <td>v{{ row.version }}</td>
                                    <td>
                                        <span
                                            class="badge"
                                            :class="
                                                row.status === 'published'
                                                    ? 'text-bg-success'
                                                    : row.status === 'archived'
                                                      ? 'text-bg-dark'
                                                      : 'text-bg-secondary'
                                            "
                                        >
                                            {{ label(row.status) }}
                                        </span>
                                    </td>
                                    <td>{{ row.responses_count || 0 }}</td>
                                    <td class="text-end">
                                        <button
                                            class="btn btn-sm btn-outline-primary me-1"
                                            @click="openEdit(row)"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-outline-danger"
                                            @click="remove(row)"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!loading && !rows.length">
                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-5"
                                    >
                                        No project application templates found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <CrudPagination
                            :current-page="pagination.current_page"
                            :last-page="pagination.last_page"
                            :prev="Boolean(pagination.prev_page_url)"
                            :next="Boolean(pagination.next_page_url)"
                            @change="load"
                        />
                    </div>
                </div>
            </div>
        </section>

        <CrudModal
            ref="modal"
            :title="isEditing ? 'Edit Template' : 'Add Template'"
            :form="form"
            :errors="errors"
            size="xl"
            @hidden="resetForm"
        >
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input v-model.trim="form.title" class="form-control" />
                    <small class="text-danger">{{ firstError("title") }}</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Code *</label>
                    <input v-model.trim="form.code" class="form-control" />
                    <small class="text-danger">{{ firstError("code") }}</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                        v-model.trim="form.description"
                        class="form-control"
                        rows="2"
                    ></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Version *</label>
                    <input
                        v-model="form.version"
                        type="number"
                        min="1"
                        class="form-control"
                    />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Phase *</label>
                    <select v-model="form.phase" class="form-select">
                        <option value="application">Application</option>
                        <option value="implementation">Implementation</option>
                        <option value="monitoring">Monitoring</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status *</label>
                    <select v-model="form.status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort Order *</label>
                    <input
                        v-model="form.sort_order"
                        type="number"
                        min="0"
                        class="form-control"
                    />
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input
                            id="template-required"
                            v-model="form.is_required"
                            type="checkbox"
                            class="form-check-input"
                        />
                        <label for="template-required" class="form-check-label">
                            Required when creating a project proposal
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="builder-heading">
                        <div>
                            <h5 class="mb-1">Form Fields</h5>
                            <p class="text-muted small mb-0">
                                Organize the form into sections, then add the
                                fields applicants need to complete.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm"
                            @click="addSection"
                        >
                            <i class="bi bi-plus-circle me-1"></i>
                            Add Section
                        </button>
                    </div>

                    <small class="text-danger">
                        {{
                            firstError("schema") ||
                            firstError("schema.sections")
                        }}
                    </small>

                    <section
                        v-for="(section, sectionIndex) in form.schema.sections"
                        :key="sectionIndex"
                        class="builder-section"
                    >
                        <header class="builder-section-header">
                            <span class="section-number">
                                {{ sectionIndex + 1 }}
                            </span>
                            <div class="flex-grow-1">
                                <label class="form-label small mb-1">
                                    Section title *
                                </label>
                                <input
                                    v-model.trim="section.title"
                                    class="form-control"
                                    placeholder="Example: Project Information"
                                    :class="{
                                        'is-invalid': builderError(
                                            sectionIndex,
                                            null,
                                            'title',
                                        ),
                                    }"
                                />
                                <div class="invalid-feedback">
                                    {{
                                        builderError(
                                            sectionIndex,
                                            null,
                                            "title",
                                        )
                                    }}
                                </div>
                            </div>
                            <div class="builder-actions">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light"
                                    title="Move section up"
                                    :disabled="sectionIndex === 0"
                                    @click="
                                        moveItem(
                                            form.schema.sections,
                                            sectionIndex,
                                            -1,
                                        )
                                    "
                                >
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light"
                                    title="Move section down"
                                    :disabled="
                                        sectionIndex ===
                                        form.schema.sections.length - 1
                                    "
                                    @click="
                                        moveItem(
                                            form.schema.sections,
                                            sectionIndex,
                                            1,
                                        )
                                    "
                                >
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Remove section"
                                    :disabled="
                                        form.schema.sections.length === 1
                                    "
                                    @click="removeSection(sectionIndex)"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </header>

                        <div class="builder-fields">
                            <article
                                v-for="(field, fieldIndex) in section.fields"
                                :key="fieldIndex"
                                class="builder-field"
                            >
                                <div class="field-toolbar">
                                    <strong>Field {{ fieldIndex + 1 }}</strong>
                                    <div class="builder-actions">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light"
                                            :disabled="fieldIndex === 0"
                                            title="Move field up"
                                            @click="
                                                moveItem(
                                                    section.fields,
                                                    fieldIndex,
                                                    -1,
                                                )
                                            "
                                        >
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light"
                                            :disabled="
                                                fieldIndex ===
                                                section.fields.length - 1
                                            "
                                            title="Move field down"
                                            @click="
                                                moveItem(
                                                    section.fields,
                                                    fieldIndex,
                                                    1,
                                                )
                                            "
                                        >
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            :disabled="
                                                section.fields.length === 1
                                            "
                                            title="Remove field"
                                            @click="
                                                removeField(section, fieldIndex)
                                            "
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Field label *
                                        </label>
                                        <input
                                            v-model.trim="field.label"
                                            class="form-control"
                                            placeholder="Question or field label"
                                            :class="{
                                                'is-invalid': builderError(
                                                    sectionIndex,
                                                    fieldIndex,
                                                    'label',
                                                ),
                                            }"
                                            @blur="generateKey(field)"
                                        />
                                        <div class="invalid-feedback">
                                            {{
                                                builderError(
                                                    sectionIndex,
                                                    fieldIndex,
                                                    "label",
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            Field type *
                                        </label>
                                        <select
                                            v-model="field.type"
                                            class="form-select"
                                            @change="changeFieldType(field)"
                                        >
                                            <option
                                                v-for="type in fieldTypes"
                                                :key="type.value"
                                                :value="type.value"
                                            >
                                                {{ type.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            Field key *
                                        </label>
                                        <input
                                            v-model.trim="field.key"
                                            class="form-control font-monospace"
                                            placeholder="project_title"
                                            :class="{
                                                'is-invalid': builderError(
                                                    sectionIndex,
                                                    fieldIndex,
                                                    'key',
                                                ),
                                            }"
                                        />
                                        <div class="invalid-feedback">
                                            {{
                                                builderError(
                                                    sectionIndex,
                                                    fieldIndex,
                                                    "key",
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">
                                            Help text
                                        </label>
                                        <input
                                            v-model.trim="field.help_text"
                                            class="form-control"
                                            placeholder="Optional instructions shown below the field"
                                        />
                                    </div>
                                    <div
                                        class="col-md-3 d-flex align-items-end pb-2"
                                    >
                                        <div class="form-check form-switch">
                                            <input
                                                :id="`required-${sectionIndex}-${fieldIndex}`"
                                                v-model="field.required"
                                                type="checkbox"
                                                class="form-check-input"
                                            />
                                            <label
                                                class="form-check-label"
                                                :for="`required-${sectionIndex}-${fieldIndex}`"
                                            >
                                                Required field
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        v-if="usesOptions(field)"
                                        class="col-12 option-builder"
                                    >
                                        <label class="form-label">
                                            Choice options *
                                        </label>
                                        <div class="input-group mb-2">
                                            <input
                                                v-model="field._optionInput"
                                                class="form-control"
                                                placeholder="Enter an option"
                                                @keyup.enter.prevent="
                                                    addOption(field)
                                                "
                                            />
                                            <button
                                                type="button"
                                                class="btn btn-outline-success"
                                                @click="addOption(field)"
                                            >
                                                Add Option
                                            </button>
                                        </div>
                                        <div class="option-list">
                                            <span
                                                v-for="(
                                                    option, optionIndex
                                                ) in field.options"
                                                :key="option"
                                                class="option-chip"
                                            >
                                                {{ option }}
                                                <button
                                                    type="button"
                                                    aria-label="Remove option"
                                                    @click="
                                                        removeOption(
                                                            field,
                                                            optionIndex,
                                                        )
                                                    "
                                                >
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </span>
                                            <span
                                                v-if="!field.options.length"
                                                class="text-muted small"
                                            >
                                                Add at least one option.
                                            </span>
                                        </div>
                                        <small class="text-danger">
                                            {{
                                                builderError(
                                                    sectionIndex,
                                                    fieldIndex,
                                                    "options",
                                                )
                                            }}
                                        </small>
                                    </div>
                                </div>
                            </article>

                            <button
                                type="button"
                                class="btn btn-outline-success btn-sm"
                                @click="addField(section)"
                            >
                                <i class="bi bi-plus-circle me-1"></i>
                                Add Field to {{ section.title || "Section" }}
                            </button>
                        </div>
                    </section>
                </div>
            </div>
            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="modal?.close()"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="saving"
                    @click="save"
                >
                    {{ saving ? "Saving..." : isEditing ? "Update" : "Save" }}
                </button>
            </template>
        </CrudModal>
    </MainLayout>
</template>

<style scoped>
.builder-heading,
.builder-section-header,
.field-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.builder-heading {
    padding: 1rem;
    background: linear-gradient(110deg, #e5f7ee, #f8fcfa);
    border: 1px solid #cfe8dc;
    border-radius: 12px;
}

.builder-heading h5 {
    color: #075b47;
}

.builder-section {
    overflow: hidden;
    margin-top: 1rem;
    background: #f8fcfa;
    border: 1px solid #cfe8dc;
    border-radius: 12px;
}

.builder-section-header {
    align-items: flex-end;
    padding: 0.9rem;
    background: #edf8f3;
    border-bottom: 1px solid #d7e9e1;
}

.section-number {
    display: grid;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    place-items: center;
    margin-bottom: 0.25rem;
    color: #fff;
    background: #159665;
    border-radius: 9px;
    font-weight: 750;
}

.builder-actions {
    display: flex;
    flex: 0 0 auto;
    gap: 0.3rem;
}

.builder-fields {
    display: grid;
    gap: 0.8rem;
    padding: 0.9rem;
}

.builder-field {
    padding: 0.9rem;
    background: #fff;
    border: 1px solid #dcebe5;
    border-left: 4px solid #1ca56c;
    border-radius: 10px;
}

.field-toolbar {
    padding-bottom: 0.65rem;
    margin-bottom: 0.8rem;
    color: #164c3e;
    border-bottom: 1px solid #e7efec;
}

.option-builder {
    padding: 0.75rem;
    background: #f3faf7;
    border: 1px dashed #9acdb8;
    border-radius: 9px;
}

.option-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.option-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.5rem 0.35rem 0.65rem;
    color: #075b47;
    background: #dff5e9;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.option-chip button {
    display: grid;
    width: 19px;
    height: 19px;
    padding: 0;
    place-items: center;
    color: #075b47;
    background: transparent;
    border: 0;
    border-radius: 50%;
}

.option-chip button:hover {
    color: #fff;
    background: #087258;
}

@media (max-width: 767.98px) {
    .builder-heading,
    .builder-section-header {
        align-items: stretch;
        flex-direction: column;
    }

    .section-number {
        display: none;
    }
}
</style>
