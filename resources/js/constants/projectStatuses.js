export const PROJECT_STATUSES = Object.freeze({
    DRAFT: "draft",
    SUBMITTED: "submitted",
    UNDER_REVIEW: "under_review",
    RETURNED: "returned",
    DEPARTMENT_APPROVED: "department_approved",
    APPROVED: "approved",
    ONGOING: "ongoing",
    COMPLETED: "completed",
    CANCELLED: "cancelled",
    REJECTED: "rejected",
});

export const PROJECT_STATUS_OPTIONS = [
    { value: "draft", label: "Draft" },
    { value: "submitted", label: "Submitted" },
    { value: "under_review", label: "Under Review" },
    { value: "returned", label: "Returned" },
    {
        value: "department_approved",
        label: "Department Approved",
    },
    { value: "approved", label: "Approved" },
    { value: "ongoing", label: "Ongoing" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
    { value: "rejected", label: "Rejected" },
];

export const getProjectStatusLabel = (status) => {
    return (
        PROJECT_STATUS_OPTIONS.find(
            (option) => option.value === status,
        )?.label ?? status
    );
};
