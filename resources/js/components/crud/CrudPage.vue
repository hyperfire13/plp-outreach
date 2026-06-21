<template>
    <div class="card shadow-sm">
        <div
            class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
        >
            <h4 class="mb-0">
                {{ title }}
            </h4>

            <button
                class="btn btn-primary"
                @click="$emit('create')"
            >
                <i class="bi bi-plus-lg me-1"></i>
                {{ addLabel }}
            </button>
        </div>

        <div class="card-body">

            <slot />

        </div>

    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },

    addLabel: {
        type: String,
        default: 'Add'
    }
})

defineEmits(['create'])
</script>
