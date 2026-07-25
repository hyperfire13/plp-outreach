<script setup>
import { computed } from "vue";

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },

  modelValue: {
    default: null,
  },

  error: {
    type: String,
    default: "",
  },

  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const value = computed({
  get() {
    return props.modelValue;
  },

  set(newValue) {
    emit("update:modelValue", newValue);
  },
});

function updateCheckbox(option, checked) {
  const current = Array.isArray(value.value)
    ? [...value.value]
    : [];

  if (checked && !current.includes(option)) {
    current.push(option);
  }

  if (!checked) {
    const index = current.indexOf(option);

    if (index >= 0) {
      current.splice(index, 1);
    }
  }

  value.value = current;
}
</script>

<template>
  <div class="mb-4">
    <label class="form-label fw-semibold">
      {{ question.question }}

      <span v-if="question.is_required" class="text-danger">
        *
      </span>
    </label>

    <small
      v-if="question.help_text"
      class="d-block text-muted mb-2"
    >
      {{ question.help_text }}
    </small>

    <input
      v-if="question.question_type === 'text'"
      v-model="value"
      type="text"
      class="form-control"
      :class="{ 'is-invalid': error }"
      :disabled="disabled"
    />

    <textarea
      v-else-if="question.question_type === 'textarea'"
      v-model="value"
      rows="4"
      class="form-control"
      :class="{ 'is-invalid': error }"
      :disabled="disabled"
    ></textarea>

    <input
      v-else-if="question.question_type === 'number'"
      v-model.number="value"
      type="number"
      class="form-control"
      :class="{ 'is-invalid': error }"
      :disabled="disabled"
    />

    <input
      v-else-if="question.question_type === 'date'"
      v-model="value"
      type="date"
      class="form-control"
      :class="{ 'is-invalid': error }"
      :disabled="disabled"
    />

    <select
      v-else-if="question.question_type === 'select'"
      v-model="value"
      class="form-select"
      :class="{ 'is-invalid': error }"
      :disabled="disabled"
    >
      <option value="">Select answer</option>
      <option
        v-for="option in question.options ?? []"
        :key="option"
        :value="option"
      >
        {{ option }}
      </option>
    </select>

    <div v-else-if="question.question_type === 'radio'">
      <div
        v-for="option in question.options ?? []"
        :key="option"
        class="form-check"
      >
        <input
          :id="`question-${question.id}-${option}`"
          v-model="value"
          class="form-check-input"
          type="radio"
          :value="option"
          :disabled="disabled"
        />

        <label
          class="form-check-label"
          :for="`question-${question.id}-${option}`"
        >
          {{ option }}
        </label>
      </div>
    </div>

    <div v-else-if="question.question_type === 'checkbox'">
      <div
        v-for="option in question.options ?? []"
        :key="option"
        class="form-check"
      >
        <input
          :id="`question-${question.id}-${option}`"
          class="form-check-input"
          type="checkbox"
          :checked="
            Array.isArray(value) && value.includes(option)
          "
          :disabled="disabled"
          @change="
            updateCheckbox(option, $event.target.checked)
          "
        />

        <label
          class="form-check-label"
          :for="`question-${question.id}-${option}`"
        >
          {{ option }}
        </label>
      </div>
    </div>

    <select
      v-else-if="question.question_type === 'boolean'"
      v-model="value"
      class="form-select"
      :class="{ 'is-invalid': error }"
      :disabled="disabled"
    >
      <option :value="null">Select answer</option>
      <option :value="true">Yes</option>
      <option :value="false">No</option>
    </select>

    <div v-if="error" class="text-danger small mt-1">
      {{ error }}
    </div>
  </div>
</template>
