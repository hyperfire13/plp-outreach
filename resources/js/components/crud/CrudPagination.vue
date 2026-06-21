<template>

    <div
        class="d-flex justify-content-between align-items-center mt-3"
    >

        <div>
            Page {{ currentPage }}
            of {{ lastPage }}
        </div>

        <div>

            <button
                class="btn btn-secondary btn-sm me-2"
                :disabled="!prev"
                @click="$emit('change', currentPage - 1)"
            >
                Previous
            </button>

            <button
                class="btn btn-secondary btn-sm"
                :disabled="!next"
                @click="$emit('change', currentPage + 1)"
            >
                Next
            </button>

        </div>

    </div>

</template>

<script setup>

defineProps({

    currentPage: Number,

    lastPage: Number,

    prev: Boolean,

    next: Boolean

})

defineEmits([
    'change'
])

</script>
