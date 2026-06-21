<template>

    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle">

            <thead class="table-light">

                <tr>

                    <th
                        v-for="column in columns"
                        :key="column.key"
                    >
                        {{ column.label }}
                    </th>

                    <th width="160">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="row in rows"
                    :key="row.id"
                >

                    <td
                        v-for="column in columns"
                        :key="column.key"
                    >
                        {{ row[column.key] }}
                    </td>

                    <td>

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

                    </td>

                </tr>

                <tr v-if="!rows.length">

                    <td
                        :colspan="columns.length + 1"
                        class="text-center text-muted"
                    >
                        No records found.
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</template>

<script setup>
defineProps({
    columns: {
        type: Array,
        required: true
    },

    rows: {
        type: Array,
        default: () => []
    }
})

defineEmits([
    'edit',
    'delete'
])
</script>
