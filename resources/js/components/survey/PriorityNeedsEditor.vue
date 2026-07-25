<script setup>
const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },

  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const needOptions = [
  "Livelihood Training",
  "Medical Mission",
  "Feeding Program",
  "Educational Assistance",
  "Disaster Preparedness",
  "Environmental Program",
  "Skills Training",
  "Mental Health",
  "Digital Literacy",
  "Youth Development",
  "Senior Citizen Support",
  "Waste Management",
  "Nutrition",
  "Others",
];

function updateNeed(index, field, value) {
  const needs = [...props.modelValue];

  needs[index] = {
    ...needs[index],
    [field]: value,
  };

  emit("update:modelValue", needs);
}

function addNeed() {
  if (props.modelValue.length >= 3) {
    return;
  }

  emit("update:modelValue", [
    ...props.modelValue,
    {
      need: "",
      priority_rank: props.modelValue.length + 1,
      description: "",
    },
  ]);
}

function removeNeed(index) {
  const needs = props.modelValue
    .filter((_, currentIndex) => currentIndex !== index)
    .map((need, currentIndex) => ({
      ...need,
      priority_rank: currentIndex + 1,
    }));

  emit("update:modelValue", needs);
}
</script>

<template>
  <div>
    <div
      v-for="(need, index) in modelValue"
      :key="index"
      class="card bg-body-tertiary border mb-3"
    >
      <div class="card-body">
        <div
          class="d-flex justify-content-between align-items-center mb-3"
        >
          <h6 class="mb-0">
            Priority {{ need.priority_rank }}
          </h6>

          <button
            v-if="!disabled"
            type="button"
            class="btn btn-sm btn-outline-danger"
            @click="removeNeed(index)"
          >
            <i class="bi bi-trash"></i>
          </button>
        </div>

        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label">Community need</label>

            <select
              :value="need.need"
              class="form-select"
              :disabled="disabled"
              @change="
                updateNeed(index, 'need', $event.target.value)
              "
            >
              <option value="">Select need</option>
              <option
                v-for="option in needOptions"
                :key="option"
                :value="option"
              >
                {{ option }}
              </option>
            </select>
          </div>

          <div class="col-md-7">
            <label class="form-label">Description</label>

            <input
              :value="need.description"
              class="form-control"
              :disabled="disabled"
              placeholder="Optional supporting details"
              @input="
                updateNeed(
                  index,
                  'description',
                  $event.target.value
                )
              "
            />
          </div>
        </div>
      </div>
    </div>

    <button
      v-if="!disabled && modelValue.length < 3"
      type="button"
      class="btn btn-outline-primary"
      @click="addNeed"
    >
      <i class="bi bi-plus-circle me-1"></i>
      Add Priority Need
    </button>
  </div>
</template>
