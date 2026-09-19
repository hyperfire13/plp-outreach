<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import SurveyResponseForm from "@/components/survey/SurveyResponseForm.vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import communityService from "@/services/communityServices";
import surveyTemplateService from "@/services/surveyTemplateService";
import surveyResponseService from "@/services/surveyResponseService";
import { downloadResponse } from "@/utils/downloadResponse";

const route = useRoute();
const router = useRouter();

const communities = ref([]);
const templates = ref([]);
const selectedTemplate = ref(null);
const initialData = ref(null);
const loading = ref(true);
const submitting = ref(false);
const errors = ref({});
const failure = ref("");
const downloadingForm = ref(false);

const responseId = computed(() => route.params.id);
const isEditing = computed(() => Boolean(responseId.value));

async function loadTemplate(id) {
    if (!id) {
        selectedTemplate.value = null;
        return;
    }

    const response = await surveyTemplateService.get(id);

    selectedTemplate.value = response.data.data;
}

async function loadOptions() {
    const [communityResponse, templateResponse] = await Promise.all([
        communityService.getAll(),
        surveyTemplateService.getList({
            status: "published",
            per_page: 100,
        }),
    ]);

    communities.value = communityResponse.data.data;
    templates.value = templateResponse.data.data.data;
}

async function loadResponse() {
    if (!isEditing.value) {
        return;
    }

    const response = await surveyResponseService.get(responseId.value);

    initialData.value = response.data.data;

    await loadTemplate(initialData.value.survey_template_id);
}

async function save(payload) {
    submitting.value = true;
    errors.value = {};
    failure.value = "";

    try {
        if (isEditing.value) {
            await surveyResponseService.update(responseId.value, payload);
        } else {
            await surveyResponseService.create(payload);
        }

        router.push({
            name: "admin-survey-responses",
        });
    } catch (error) {
        errors.value = error.response?.data?.errors ?? {};
        const firstValidationError = Object.values(errors.value)
            .flat()
            .find(Boolean);

        failure.value =
            firstValidationError ??
            error.response?.data?.message ??
            "Unable to save survey response.";

        window.scrollTo({ top: 0, behavior: "smooth" });
    } finally {
        submitting.value = false;
    }
}

async function downloadBlankForm() {
    if (!selectedTemplate.value || downloadingForm.value) {
        return;
    }

    downloadingForm.value = true;

    try {
        const response = await surveyTemplateService.downloadPdf(
            selectedTemplate.value.id,
        );
        downloadResponse(
            response,
            `survey-template-v${selectedTemplate.value.version}.pdf`,
        );
    } catch (error) {
        window.alert(
            error.response?.data?.message ??
                "Unable to download the blank survey form.",
        );
    } finally {
        downloadingForm.value = false;
    }
}

onMounted(async () => {
    loading.value = true;

    try {
        await loadOptions();
        await loadResponse();
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <MainLayout>
        <section class="content">
            <div class="container-fluid">
                <div
                    class="mb-3 d-flex justify-content-between align-items-start gap-3"
                >
                    <div>
                        <h1 class="h3 mb-1">
                            {{
                                isEditing
                                    ? "Edit Survey Response"
                                    : "Conduct Survey"
                            }}
                        </h1>

                        <p class="text-muted mb-0">
                            Encode the community profile and priority needs.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-success"
                        :disabled="!selectedTemplate || downloadingForm"
                        @click="downloadBlankForm"
                    >
                        <span
                            v-if="downloadingForm"
                            class="spinner-border spinner-border-sm me-1"
                        ></span>
                        <i v-else class="bi bi-file-earmark-pdf me-1"></i>
                        {{
                            downloadingForm
                                ? "Preparing PDF..."
                                : "Download Blank Form"
                        }}
                    </button>
                </div>

                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>

                <div
                    v-if="failure"
                    class="alert alert-danger alert-dismissible fade show"
                    role="alert"
                >
                    {{ failure }}
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        @click="failure = ''"
                    ></button>
                </div>

                <SurveyResponseForm
                    v-if="!loading"
                    :communities="communities"
                    :templates="templates"
                    :template="selectedTemplate"
                    :initial-data="initialData"
                    :errors="errors"
                    :submitting="submitting"
                    @template-change="loadTemplate"
                    @save-draft="save"
                    @submit-survey="save"
                />
            </div>
        </section>
    </MainLayout>
</template>
