<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import SurveyResponseForm from "@/components/survey/SurveyResponseForm.vue";
import { communityApi } from "@/api/communities";
import surveyTemplateService from "@/services/surveyTemplateService";
import surveyResponseService from "@/services/surveyResponseService";

const route = useRoute();
const router = useRouter();

const communities = ref([]);
const templates = ref([]);
const selectedTemplate = ref(null);
const initialData = ref(null);
const loading = ref(true);
const submitting = ref(false);
const errors = ref({});

const responseId = computed(() => route.params.id);
const isEditing = computed(() => Boolean(responseId.value));

async function loadTemplate(id) {
  if (!id) {
    selectedTemplate.value = null;
    return;
  }

  const response = await surveyTemplateService.show(id);

  selectedTemplate.value = response.data.data;
}

async function loadOptions() {
  const [communityResponse, templateResponse] =
    await Promise.all([
      communityApi.all(),
      surveyTemplateService.list({
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

  const response = await surveyResponseService.show(
    responseId.value
  );

  initialData.value = response.data.data;

  await loadTemplate(
    initialData.value.survey_template_id
  );
}

async function save(payload) {
  submitting.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await surveyResponseService.update(
        responseId.value,
        payload
      );
    } else {
      await surveyResponseService.create(payload);
    }

    router.push({
      name: "admin-survey-responses",
    });
  } catch (error) {
    errors.value = error.response?.data?.errors ?? {};

    if (!error.response?.data?.errors) {
      window.alert(
        error.response?.data?.message ??
          "Unable to save survey response."
      );
    }
  } finally {
    submitting.value = false;
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
  <section class="content">
    <div class="container-fluid">
      <div class="mb-3">
        <h1 class="h3 mb-1">
          {{ isEditing ? "Edit Survey Response" : "Conduct Survey" }}
        </h1>

        <p class="text-muted mb-0">
          Encode the community profile and priority needs.
        </p>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
      </div>

      <SurveyResponseForm
        v-else
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
</template>
