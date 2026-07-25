<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import SurveyResponseForm from "@/components/survey/SurveyResponseForm.vue";
import surveyResponseService from "@/services/surveyResponseService";


const route = useRoute();

const responseData = ref(null);
const loading = ref(true);

onMounted(async () => {
  try {
    const response = await surveyResponseService.show(
      route.params.id
    );

    responseData.value = response.data.data;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section class="content">
    <div class="container-fluid">
      <div class="mb-3">
        <h1 class="h3 mb-1">Survey Response</h1>
        <p class="text-muted mb-0">
          Review the complete community survey submission.
        </p>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
      </div>

      <SurveyResponseForm
        v-else-if="responseData"
        :communities="[responseData.community]"
        :templates="[responseData.template]"
        :template="{
          ...responseData.template,
          questions: responseData.answers
            .map((answer) => answer.question)
            .filter(Boolean),
        }"
        :initial-data="responseData"
        readonly
      />
    </div>
  </section>
</template>
