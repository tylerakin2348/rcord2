<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Choose a Plan</label>
    <div class="space-y-2">
      <div v-for="plan in plans" :key="plan.id" class="border rounded p-3 flex items-center justify-between" :class="selectedPlanId === plan.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
        <div>
          <span class="font-bold text-gray-900">{{ plan.name.charAt(0).toUpperCase() + plan.name.slice(1) }}</span>
          <span class="ml-2 text-xs text-gray-500">{{ plan.description }}</span>
        </div>
        <button type="button" @click="$emit('update:selectedPlanId', plan.id)" class="ml-4 px-2 py-1 rounded bg-blue-600 text-white text-xs font-medium" :class="selectedPlanId === plan.id ? 'opacity-100' : 'opacity-70'">
          {{ selectedPlanId === plan.id ? 'Selected' : 'Choose' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const props = defineProps({
  selectedPlanId: Number
})
const plans = ref([])

const fetchPlans = async () => {
  const response = await window.axios.get('/api/plans')
  plans.value = response.data
}

onMounted(fetchPlans)
</script>
