<template>

<div
    class="modal fade"
    tabindex="-1"
    ref="modalRef"
>

    <div class="modal-dialog">

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

                <div
                    v-for="field in fields"
                    :key="field.key"
                    class="mb-3"
                >

                    <label class="form-label">

                        {{ field.label }}

                    </label>

                    <input
                        v-if="field.type !== 'select'"
                        v-model="form[field.key]"
                        :type="field.type"
                        class="form-control"
                    >

                    <select
                        v-else
                        v-model="form[field.key]"
                        class="form-select"
                    >

                        <option
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>

                    </select>

                    <small class="text-danger">

                        {{ errors[field.key] }}

                    </small>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    @click="close"
                >
                    Cancel
                </button>

                <button
                    class="btn btn-primary"
                    @click="$emit('submit')"
                >
                    Save
                </button>

            </div>

        </div>

    </div>

</div>

</template>

<script setup>

import { Modal } from 'bootstrap'
import { ref, onMounted } from 'vue'

const modalRef = ref()
let modal

defineProps({

    title: String,

    fields: Array,

    form: Object,

    errors: Object

})

const close = () => modal.hide()

const open = () => modal.show()

defineExpose({
    open,
    close
})

onMounted(() => {
    modal = new Modal(modalRef.value)
})

</script>
