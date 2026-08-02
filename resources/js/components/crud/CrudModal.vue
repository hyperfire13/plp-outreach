<template>
    <div
        class="modal fade"
        tabindex="-1"
        ref="modalRef"
    >
        <div
            class="modal-dialog modal-dialog-centered"
            :class="`modal-${size}`"
        >
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ title }}
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        @click="close"
                    />
                </div>

                <div class="modal-body">
                    <slot v-if="$slots.default" />

                    <div v-else class="row">
                        <div
                            v-for="field in fields"
                            :key="field.key"
                            class="mb-3"
                            :class="field.col || 'col-md-6 col-12'"
                        >
                            <label class="form-label">
                                {{ field.label }}

                                <span
                                    v-if="field.required"
                                    class="text-danger"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                v-if="isInput(field)"
                                v-model="form[field.key]"
                                :type="field.type"
                                class="form-control"
                                :placeholder="field.placeholder || field.label"
                            >

                            <textarea
                                v-else-if="field.type === 'textarea'"
                                v-model="form[field.key]"
                                class="form-control"
                                :rows="field.rows || 3"
                                :placeholder="field.placeholder || field.label"
                            />

                            <select
                                v-else-if="field.type === 'select'"
                                v-model="form[field.key]"
                                class="form-select"
                            >
                                <option value="">
                                    {{ field.placeholder || `Select ${field.label}` }}
                                </option>

                                <option
                                    v-for="option in field.options || []"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>

                            <div
                                v-else-if="field.type === 'checkbox'"
                                class="form-check"
                            >
                                <input
                                    v-model="form[field.key]"
                                    class="form-check-input"
                                    type="checkbox"
                                    :id="field.key"
                                >

                                <label
                                    class="form-check-label"
                                    :for="field.key"
                                >
                                    {{ field.checkboxLabel || field.label }}
                                </label>
                            </div>

                            <small
                                v-if="errors[field.key]"
                                class="text-danger"
                            >
                                {{ getError(field.key) }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <slot v-if="$slots.footer" name="footer" />

                    <template v-else>
                        <button
                            class="btn btn-secondary"
                            type="button"
                            @click="close"
                        >
                            Cancel
                        </button>

                        <button
                            class="btn btn-primary"
                            type="button"
                            @click="$emit('submit')"
                        >
                            {{ submitLabel }}
                        </button>
                    </template>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { Modal } from 'bootstrap'
import { ref, onMounted, onBeforeUnmount } from 'vue'

const modalRef = ref(null)
let modal = null

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    fields: {
        type: Array,
        default: () => []
    },
    form: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    submitLabel: {
        type: String,
        default: 'Save'
    },
    size: {
        type: String,
        default: 'lg'
    }
})

const emit = defineEmits(['submit', 'hidden'])

const inputTypes = [
    'text',
    'email',
    'password',
    'number',
    'date',
    'time',
    'datetime-local'
]

const isInput = (field) => inputTypes.includes(field.type)

const getError = (key) => {
    const error = props.errors[key]

    return Array.isArray(error)
        ? error[0]
        : error
}

const open = () => {
    modal?.show()
}

const close = () => {
    modal?.hide()
}

const handleHidden = () => {
    emit('hidden')
}

defineExpose({
    open,
    close
})

onMounted(() => {
    modal = new Modal(modalRef.value, {
        backdrop: 'static',
        keyboard: false
    })

    modalRef.value.addEventListener(
        'hidden.bs.modal',
        handleHidden
    )
})

onBeforeUnmount(() => {
    modalRef.value?.removeEventListener(
        'hidden.bs.modal',
        handleHidden
    )
    modal?.dispose()
})
</script>
