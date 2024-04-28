import { createApp } from 'vue';
import RepairFormPage from './vue-sfc/RepairFormPage.vue';
import RepairFormSteps from './vue-sfc/RepairFormSteps.vue';

const app = createApp({});
app.component('RepairFormPage', RepairFormPage);
app.component('RepairFormSteps', RepairFormSteps);
app.mount('#vue-app');
