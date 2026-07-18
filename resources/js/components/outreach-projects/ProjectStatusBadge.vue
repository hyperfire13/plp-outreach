<script setup>
import { computed } from "vue";
import { getProjectStatusLabel } from "@/constants/projectStatuses";

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const badgeClass = computed(() => {
    const classes = {
        draft: "text-bg-secondary",
        submitted: "text-bg-primary",
        under_review: "text-bg-info",
        returned: "text-bg-warning",
        department_approved: "text-bg-info",
        approved: "text-bg-success",
        ongoing: "text-bg-primary",
        completed: "text-bg-success",
        cancelled: "text-bg-dark",
        rejected: "text-bg-danger",
    };

    return classes[props.status] ?? "text-bg-secondary";
});
</script>

<template>
    <span class="badge" :class="badgeClass">
        {{ getProjectStatusLabel(status) }}
    </span>
</template>
