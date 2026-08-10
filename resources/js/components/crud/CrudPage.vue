<template>
    <div class="card shadow-sm">
        <div
            class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
        >
            <div>
                <h4 class="mb-0">{{ title }}</h4>
                <p v-if="subtitle" class="text-muted mb-0 mt-1">
                    {{ subtitle }}
                </p>
            </div>

            <button
                v-if="canCreate"
                class="btn btn-primary"
                type="button"
                :disabled="loading"
                @click="$emit('create')"
            >
                <i class="bi bi-plus-lg me-1"></i>
                {{ createLabel || addLabel }}
            </button>
        </div>

        <div class="card-body">

            <div v-if="$slots.filters" class="mb-3">
                <slot name="filters" />
            </div>

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

    subtitle: {
        type: String,
        default: ''
    },

    addLabel: {
        type: String,
        default: 'Add'
    },

    createLabel: {
        type: String,
        default: ''
    },

    canCreate: {
        type: Boolean,
        default: true
    },

    loading: {
        type: Boolean,
        default: false
    }
})

defineEmits(['create'])
</script>
