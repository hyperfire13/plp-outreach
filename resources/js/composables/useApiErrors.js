import { ref } from "vue";

export function useApiErrors() {
    const errors = ref({});
    const generalError = ref("");

    const clearErrors = () => {
        errors.value = {};
        generalError.value = "";
    };

    const captureError = (error) => {
        clearErrors();

        const status = error.response?.status;
        const responseData = error.response?.data;

        if (status === 422) {
            errors.value = responseData?.errors ?? {};
            generalError.value =
                responseData?.message ?? "Please check the form.";
            return;
        }

        if (status === 403) {
            generalError.value =
                responseData?.message ??
                "You are not authorized to perform this action.";
            return;
        }

        if (status === 404) {
            generalError.value =
                responseData?.message ?? "The requested record was not found.";
            return;
        }

        generalError.value =
            responseData?.message ??
            "An unexpected error occurred. Please try again.";
    };

    const firstError = (field) => {
        return errors.value?.[field]?.[0] ?? "";
    };

    return {
        errors,
        generalError,
        clearErrors,
        captureError,
        firstError,
    };
}
