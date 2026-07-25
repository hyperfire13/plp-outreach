<script setup>
import { computed, reactive, watch } from "vue";
import SurveyAnswerField from "./SurveyAnswerField.vue";
import PriorityNeedsEditor from "./PriorityNeedsEditor.vue";

const props = defineProps({
  communities: {
    type: Array,
    default: () => [],
  },

  templates: {
    type: Array,
    default: () => [],
  },

  template: {
    type: Object,
    default: null,
  },

  initialData: {
    type: Object,
    default: null,
  },

  errors: {
    type: Object,
    default: () => ({}),
  },

  submitting: {
    type: Boolean,
    default: false,
  },

  readonly: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "template-change",
  "save-draft",
  "submit-survey",
]);

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
  community_id: "",
  survey_template_id: "",
  survey_date: today,
  academic_department: "",
  conducted_by: "",
  suggested_outreach_program: "",
  remarks: "",
  answers: {},
  priority_needs: [],
});

const sections = computed(() => {
  const questions = props.template?.questions ?? [];

  return questions.reduce((result, question) => {
    const section = question.section || "General";

    if (!result[section]) {
      result[section] = [];
    }

    result[section].push(question);

    return result;
  }, {});
});

function answerError(questionId) {
  const entries = Object.entries(props.errors);

  const match = entries.find(([key]) =>
    key.includes(`survey_question_id`)
  );

  return match?.[1]?.[0] ?? "";
}

function buildPayload(status) {
  return {
    community_id: form.community_id,
    survey_template_id: form.survey_template_id,
    survey_date: form.survey_date,
    academic_department: form.academic_department || null,
    conducted_by: form.conducted_by || null,
    suggested_outreach_program:
      form.suggested_outreach_program || null,
    remarks: form.remarks || null,
    status,
    answers: Object.entries(form.answers).map(
      ([questionId, value]) => ({
        survey_question_id: Number(questionId),
        value,
      })
    ),
    priority_needs: form.priority_needs,
  };
}

function saveDraft() {
  emit("save-draft", buildPayload("draft"));
}

function submitSurvey() {
  const confirmed = window.confirm(
    "Submit this survey? Submitted responses can no longer be edited."
  );

  if (confirmed) {
    emit("submit-survey", buildPayload("submitted"));
  }
}

watch(
  () => form.survey_template_id,
  (templateId, oldTemplateId) => {
    if (
      oldTemplateId &&
      String(templateId) !== String(oldTemplateId)
    ) {
      form.answers = {};
    }

    if (templateId) {
      emit("template-change", templateId);
    }
  }
);

watch(
  () => props.initialData,
  (data) => {
    if (!data) {
      return;
    }

    form.community_id = data.community_id ?? "";
    form.survey_template_id =
      data.survey_template_id ?? "";
    form.survey_date =
      data.survey_date?.slice?.(0, 10) ?? today;
    form.academic_department =
      data.academic_department ?? "";
    form.conducted_by = data.conducted_by ?? "";
    form.suggested_outreach_program =
      data.suggested_outreach_program ?? "";
    form.remarks = data.remarks ?? "";

    form.answers = Object.fromEntries(
      (data.answers ?? []).map((answer) => {
        let value = answer.answer_text;

        switch (answer.question?.question_type) {
          case "number":
            value = answer.answer_number;
            break;

          case "date":
            value = answer.answer_date?.slice?.(0, 10);
            break;

          case "boolean":
            value = answer.answer_boolean;
            break;

          case "checkbox":
            value = answer.answer_json ?? [];
            break;
        }

        return [answer.survey_question_id, value];
      })
    );

    form.priority_needs = (data.priority_needs ?? []).map(
      (need) => ({
        need: need.need,
        priority_rank: need.priority_rank,
        description: need.description ?? "",
      })
    );
  },
  {
    immediate: true,
  }
);
</script>

<template>
  <form @submit.prevent>
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="card-title mb-0">Survey Information</h5>
      </div>

      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Community</label>

            <select
              v-model="form.community_id"
              class="form-select"
              :class="{ 'is-invalid': errors.community_id }"
              :disabled="readonly"
            >
              <option value="">Select community</option>
              <option
                v-for="community in communities"
                :key="community.id"
                :value="community.id"
              >
                {{ community.name }}
              </option>
            </select>

            <div class="invalid-feedback">
              {{ errors.community_id?.[0] }}
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Survey template</label>

            <select
              v-model="form.survey_template_id"
              class="form-select"
              :class="{
                'is-invalid': errors.survey_template_id,
              }"
              :disabled="readonly"
            >
              <option value="">Select survey template</option>
              <option
                v-for="item in templates"
                :key="item.id"
                :value="item.id"
              >
                {{ item.title }} v{{ item.version }}
              </option>
            </select>

            <div class="invalid-feedback">
              {{ errors.survey_template_id?.[0] }}
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Survey date</label>

            <input
              v-model="form.survey_date"
              type="date"
              class="form-control"
              :max="today"
              :class="{ 'is-invalid': errors.survey_date }"
              :disabled="readonly"
            />

            <div class="invalid-feedback">
              {{ errors.survey_date?.[0] }}
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">
              Academic department
            </label>

            <input
              v-model="form.academic_department"
              class="form-control"
              :disabled="readonly"
            />
          </div>

          <div class="col-md-4">
            <label class="form-label">Conducted by</label>

            <input
              v-model="form.conducted_by"
              class="form-control"
              :disabled="readonly"
            />
          </div>
        </div>
      </div>
    </div>

    <template v-if="template">
      <div
        v-for="(questions, section) in sections"
        :key="section"
        class="card mb-3"
      >
        <div class="card-header">
          <h5 class="card-title mb-0">{{ section }}</h5>
        </div>

        <div class="card-body">
          <SurveyAnswerField
            v-for="question in questions"
            :key="question.id"
            v-model="form.answers[question.id]"
            :question="question"
            :error="answerError(question.id)"
            :disabled="readonly"
          />
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">Priority Needs</h5>
        </div>

        <div class="card-body">
          <PriorityNeedsEditor
            v-model="form.priority_needs"
            :disabled="readonly"
          />
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">
            Outreach Recommendation
          </h5>
        </div>

        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">
              Suggested outreach program
            </label>

            <textarea
              v-model="form.suggested_outreach_program"
              rows="4"
              class="form-control"
              :disabled="readonly"
            ></textarea>
          </div>

          <div>
            <label class="form-label">Remarks</label>

            <textarea
              v-model="form.remarks"
              rows="3"
              class="form-control"
              :disabled="readonly"
            ></textarea>
          </div>
        </div>
      </div>
    </template>

    <div
      v-else
      class="alert alert-info"
    >
      Select a survey template to display its questions.
    </div>

    <div
      v-if="!readonly"
      class="d-flex justify-content-end gap-2"
    >
      <button
        type="button"
        class="btn btn-outline-secondary"
        :disabled="submitting"
        @click="saveDraft"
      >
        Save as Draft
      </button>

      <button
        type="button"
        class="btn btn-success"
        :disabled="submitting || !template"
        @click="submitSurvey"
      >
        <span
          v-if="submitting"
          class="spinner-border spinner-border-sm me-1"
        ></span>
        Submit Survey
      </button>
    </div>
  </form>
</template>
