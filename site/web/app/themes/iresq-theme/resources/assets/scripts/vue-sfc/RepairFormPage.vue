<template>
  <RepairFormSteps v-model="currentStep" />
  <transition name="fade" mode="out-in">
    <div v-if="!currentStep" key="type">
      <label v-for="category in categories" :key="category.termId" class="form-card device-card" @click="changeStep('type', category)">
        <input v-model="typeSlug" class="card-input-element" type="radio" name="Type" :value="category.slug">
        <div class="card-input device-input">
          <h6 class="card-heading">{{ category.name }}</h6>
          <img :src="category.thumbnail" class="card-image" :alt="category.name">
        </div>
      </label>
    </div>

    <div v-else-if="currentStep === 'type'" key="brand">
      <label v-for="category in selectedType.brands" :key="category.termId" class="form-card device-card" @click="changeStep('brand', category)">
        <input v-model="brandSlug" class="card-input-element" type="radio" name="Brand" :value="category.slug">
        <div class="card-input device-input">
          <h6 class="card-heading">{{ category.name }}</h6>
          <img :src="category.thumbnail" class="card-image" :alt="category.name">
        </div>
      </label>
    </div>

    <div v-else key="model">
      <label for="modelSelected" class="tw-block tw-mb-8 tw-text-4xl">Select a Device Model</label>
      <select id="modelSelected" v-model="modelSlug" @change="changeStep('model', null)" class="iresq-text-input">
      <option value="">-- Choose an Option --</option>
        <option v-for="category in selectedBrandModelSingleGrouping" :key="category.id" :value="category.slug">{{ category.name }}</option>
        <optgroup v-for="(categories, modelType) in selectedBrandModelGroupings" :key="modelType" :label="modelType">
          <option v-for="category in categories" :key="category.id" :value="category.slug">{{ category.name }}</option>
        </optgroup>
      </select>
      <!-- <label v-for="category in selectedBrandModelSingleGrouping" :key="category.termId" class="form-card device-card" @click="changeStep('model', category)">
        <input v-model="modelSlug" class="card-input-element" type="radio" name="Model" :value="category.slug">
        <div class="card-input device-input">
          <h6 class="card-heading">{{ category.name }}</h6>
          <img :src="category.thumbnail" class="card-image" :alt="category.name">
        </div>
      </label>

      <div class="tw-pt-5" v-for="(categories, modelType) in selectedBrandModelGroupings" :key="modelType">
        <p class="tw-font-bold tw-text-4xl">{{ modelType }}</p>
        <label v-for="category in categories" :key="category.termId" class="form-card device-card" @click="changeStep('model', category)">
          <input v-model="modelSlug" class="card-input-element" type="radio" name="Model" :value="category.slug">
          <div class="card-input device-input">
            <h6 class="card-heading">{{ category.name }}</h6>
            <img :src="category.thumbnail" class="card-image" :alt="category.name">
          </div>
        </label>
      </div> -->
    </div>
  </transition>
</template>

<script setup lang="ts">
  import {
    ref, computed, PropType,
  } from 'vue';
  import { DeviceTypeCategory } from '../types/RepairCategory';
  import RepairFormSteps from './RepairFormSteps.vue';

  const props = defineProps({
    categories: {
      type: Array as PropType<Array<DeviceTypeCategory>>,
      default: () => [],
    },
  });

  const currentStep = ref('');
  const typeSlug = ref('');
  const brandSlug = ref('');
  const modelSlug = ref('');

  const selectedType = computed(() => props.categories?.find((x) => x.slug === typeSlug.value));
  const selectedBrand = computed(() => selectedType.value?.brands?.find((m) => m.slug === brandSlug.value));
  const selectedModel = computed(() => selectedBrand.value?.models?.find((m) => m.slug === modelSlug.value));
  const selectedBrandModelGroupings = computed(() => {
    if(!selectedBrand.value) return;
    const modelNamesOnly = selectedBrand.value?.models?.map(m => {
      m.parsedName = m.name.split('(')[0];
      return m;
    })
    const grouped = groupBy(modelNamesOnly, "parsedName");
    Object.keys(grouped).filter(key => grouped[key].length == 1).forEach(key => delete grouped[key])
    return grouped;
  })

  const selectedBrandModelSingleGrouping = computed(() => {
    if(!selectedBrand.value) return;
    const modelNamesOnly = selectedBrand.value?.models?.map(m => {
      m.parsedName = m.name.split('(')[0];
      return m;
    })
    const grouped = groupBy(modelNamesOnly, "parsedName");
    Object.keys(grouped).filter(key => grouped[key].length !== 1).map(key => delete grouped[key])
    let models = [];
    Object.keys(grouped).forEach(key => models = [...models, ...grouped[key]])
    return models;
  })

  function changeStep(step, category) {
    if((step === 'type' && category.brands.length === 0) || (step === 'brand' && category.models.length === 0)) {
      window.location.href = category.url;
      return;
    }
    if(step === 'model') {
      window.location.href = selectedModel.value.url;
      return;
    }
    currentStep.value = step;
  }

  function groupBy(items, key) {
    return items.reduce((result, item) => ({
      ...result, [item[key]]: [
        ...(result[item[key]] || []),
        item,
      ],
    }), {});
  }
</script>
