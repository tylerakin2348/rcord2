<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[100000] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="absolute inset-0 bg-stone-900/50 backdrop-blur-[1px]"
          @click="closeModal"
        />
        <div
          class="modal-panel relative w-full max-w-md rounded-xl bg-white shadow-xl border border-stone-200/80 p-6 max-h-[90vh] overflow-y-auto"
          @click.stop
        >
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-stone-900">Upgrade Plan</h2>
            <button
              type="button"
              class="text-stone-400 hover:text-stone-600 transition-colors"
              @click="closeModal"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div v-if="plan" class="mb-6">
            <p class="font-bold text-lg text-stone-900 mb-1">{{ plan.name.charAt(0).toUpperCase() + plan.name.slice(1) }}</p>
            <p class="text-sm text-stone-700 mb-2">{{ plan.description }}</p>
            <div class="text-xl font-semibold text-blue-700 mb-2">Amount: {{ formatAmount(plan.name) }}</div>
          </div>
          <form @submit.prevent="submitPayment">
            <div class="mb-4">
              <label class="block text-sm font-medium text-stone-700 mb-2">Name on Card</label>
              <input
                v-model="name"
                type="text"
                class="border border-stone-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-stone-500 focus:border-transparent"
                placeholder="Full Name"
                required
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-stone-700 mb-2">Card Details</label>
              <div id="stripe-payment-element" />
            </div>
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
              {{ error }}
            </div>
            <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
              {{ successMessage }}
            </div>
            <div class="flex justify-end gap-3">
              <button
                type="button"
                class="px-4 py-2 border border-stone-300 rounded-lg text-sm font-medium text-stone-700 hover:bg-stone-50 transition-colors"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50"
              >
                <span v-if="loading">Processing...</span>
                <span v-else>Pay & Upgrade</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
  getStripeInstance,
  mountCardElement,
  unmountCardElement,
  getCardElement,
} from '../stripe.js'

const props = defineProps({
  isOpen: Boolean,
  plan: Object,
})
const emit = defineEmits(['close', 'success'])
const error = ref('')
const successMessage = ref('')
const loading = ref(false)

const closeModal = () => {
  unmountCardElement()
  emit('close')
}

const handleEscape = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    closeModal()
  }
}

const mountStripeElements = () => {
  if (!window.Stripe) {
    error.value = 'Stripe.js not loaded'
    return
  }
  const container = document.getElementById('stripe-payment-element')
  if (!container) {
    error.value = 'Stripe container not found.'
    return
  }
  mountCardElement('stripe-payment-element')
}

const name = ref('')
const email = ref('')

const submitPayment = async () => {
  loading.value = true
  error.value = ''
  successMessage.value = ''
  let clientSecret
  try {
    const response = await window.axios.post('/api/stripe/payment-intent', {
      plan_id: props.plan.id,
      name: name.value,
      email: email.value,
    })
    clientSecret = response.data.client_secret
  } catch (e) {
    error.value = 'Failed to create payment intent.'
    loading.value = false
    return
  }
  const stripe = getStripeInstance()
  const cardElement = getCardElement()
  const { error: stripeError } = await stripe.confirmCardPayment(clientSecret, {
    payment_method: {
      card: cardElement,
      billing_details: {
        name: name.value,
        email: email.value,
      },
    },
  })
  if (stripeError) {
    error.value = stripeError.message
    loading.value = false
    return
  }
  try {
    await window.axios.post('/api/user/plan', { plan_id: props.plan.id })
    successMessage.value = 'Payment successful! Your plan has been upgraded.'
    setTimeout(() => {
      emit('success')
      closeModal()
    }, 1500)
  } catch (e) {
    error.value = 'Payment succeeded, but failed to update your plan. Please contact support.'
  }
  loading.value = false
}

const formatAmount = (planName) => {
  if (planName === 'base') return '$5.00'
  if (planName === 'full') return '$10.00'
  return '$0.00'
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    error.value = ''
    successMessage.value = ''
    setTimeout(() => {
      mountStripeElements()
    }, 300)
    window.addEventListener('keydown', handleEscape)
  } else {
    window.removeEventListener('keydown', handleEscape)
    unmountCardElement()
  }
})

onMounted(() => {
  if (props.isOpen) {
    setTimeout(() => {
      mountStripeElements()
    }, 300)
    window.addEventListener('keydown', handleEscape)
  }
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
  unmountCardElement()
})
</script>
