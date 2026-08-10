<template>

    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th
                        v-for="column in columns"
                        :key="column.key"
                    >
                        {{ column.label }}
                    </th>

                    <th v-if="showActions" width="160" class="text-end">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr v-if="loading">
                    <td
                        :colspan="columns.length + (showActions ? 1 : 0)"
                        class="text-center py-5"
                    >
                        <span class="spinner-border text-primary" role="status" />
                    </td>
                </tr>

                <tr
                    v-for="row in loading ? [] : rows"
                    :key="row.id"
                >

                    <td
                        v-for="column in columns"
                        :key="column.key"
                    >
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="row[column.key]"
                        >
                            {{ row[column.key] }}
                        </slot>
                    </td>

                    <td v-if="showActions" class="text-end text-nowrap">

                        <slot v-if="$slots.actions" name="actions" :row="row" />

                        <template v-else>

                        <button
                            class="btn btn-warning btn-sm me-1"
                            @click="$emit('edit', row)"
                        >
                            Edit
                        </button>

                        <button
                            class="btn btn-danger btn-sm"
                            @click="$emit('delete', row)"
                        >
                            Delete
                        </button>

                        </template>

                    </td>

                </tr>

                <tr v-if="!loading && !rows.length">

                    <td
                        :colspan="columns.length + (showActions ? 1 : 0)"
                        class="text-center text-muted py-4"
                    >
                        {{ emptyMessage }}
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</template>

<script setup>
import { computed, useSlots } from 'vue'

const props = defineProps({
    columns: {
        type: Array,
        required: true
    },

    rows: {
        type: Array,
        default: () => []
    },

    loading: {
        type: Boolean,
        default: false
    },

    emptyMessage: {
        type: String,
        default: 'No records found.'
    },

    actions: {
        type: Boolean,
        default: true
    }
})

const slots = useSlots()
const showActions = computed(() => props.actions || Boolean(slots.actions))

defineEmits([
    'edit',
    'delete'
])
</script>
