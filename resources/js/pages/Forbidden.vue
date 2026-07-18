<template>
    <div
        class="min-vh-100 d-flex align-items-center justify-content-center bg-body-tertiary"
    >
        <div class="text-center px-3">
            <h1 class="display-1 fw-bold">403</h1>

            <h2 class="h4">
                Access denied
            </h2>

            <p class="text-secondary">
                You do not have permission to access this page.
            </p>

            <RouterLink
                :to="{ name: 'dashboard' }"
                class="btn btn-primary"
            >
                Return to Dashboard
            </RouterLink>
        </div>
    </div>
</template>
