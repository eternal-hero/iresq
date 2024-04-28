<template>
  <div class="tw-w-full tw-pt-6 tw-pb-24">
    <div class="tw-flex tw-justify-center">
      <div v-for="(step, index) in steps" :key="step.key" class="tw-flex tw-items-center" :class="[index !== steps.length - 1 ? 'tw-w-full' : '']">
        <div class="tw-relative tw-flex tw-flex-col tw-items-center" :class="{'tw-cursor-pointer': index <= currentStepNumber}" @click="changeStep(step, index)">
          <div class="tw-rounded-full tw-transition tw-duration-500 tw-ease-in-out tw-border-2 tw-h-10 tw-w-10 tw-flex tw-items-center tw-justify-center tw-border-white tw-py-3 tw-font-bold" :class="[index > currentStepNumber ? 'tw-border-primary' : 'tw-text-white tw-bg-primary tw-border-primary']">
            <div v-if="index <= currentStepNumber">
              <i class="fas fa-check" />
            </div>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <div class="tw-absolute tw-top-0 tw-text-center tw-mt-12 tw-w-32 m:tw-w-48 tw-font-medium tw-uppercase" :class="[index <= currentStepNumber ? 'tw-font-bold' : '']">
            {{ step.description }}
          </div>
        </div>
        <div class="tw-w-full tw-rounded tw-items-center tw-align-middle tw-align-center tw-flex-1 tw-mx-3" style="background-color: #edf2f7">
          <div class="tw-w-0 tw-py-1 tw-rounded" style="background-color: #ff5965" :style="{'width': index <= currentStepNumber ? '100%' : ''}" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import {
    ref, computed,
  } from 'vue';

  const props = defineProps({
    modelValue: {
      type: String,
      default: '',
    },
  });
  const emit = defineEmits(['update:modelValue']);

  const currentStep = computed({
    get: () => props.modelValue,
    set(val) {
      emit('update:modelValue', val);
    },
  });

  const steps = ref([
    {
      key: 'type',
      description: 'Device Type',
      actualStep: '',
    },
    {
      key: 'brand',
      description: 'Device Brand',
      actualStep: 'type',
    },
    {
      key: 'model',
      description: 'Device Model',
      actualStep: 'brand',
    },
    {
      key: 'estimate',
      description: 'Estimate',
      actualStep: 'estimate',
    },
  ]);

  const currentStepNumber = computed(() => steps.value.findIndex((m) => m.key === currentStep.value));

  function changeStep(step, index) {
    if (index <= currentStepNumber.value) {
      currentStep.value = step.actualStep;
    }
  }
</script>
