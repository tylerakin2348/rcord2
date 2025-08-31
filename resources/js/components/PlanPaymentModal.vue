<template>
    <div v-show="isOpen" class="fixed inset-0 flex items-center justify-center z-50" style="background: rgba(0,0,0,0.5);">
      <div class="bg-white rounded-lg p-8 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Upgrade Plan</h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div v-if="plan" class="mb-6">
          <p class="font-bold text-lg text-gray-900 mb-1">{{ plan.name.charAt(0).toUpperCase() + plan.name.slice(1) }}</p>
          <p class="text-sm text-gray-700 mb-2">{{ plan.description }}</p>
          <div class="text-xl font-semibold text-blue-700 mb-2">Amount: {{ formatAmount(plan.name) }}</div>
        </div>
        <form @submit.prevent="submitPayment">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name on Card</label>
            <input v-model="name" type="text" class="border rounded p-2 w-full" placeholder="Full Name" required />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Card Details</label>
            <div id="stripe-payment-element"></div>
          </div>
          <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ error }}</div>
          <div class="flex justify-end space-x-3">
            <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" :disabled="loading" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
              <span v-if="loading">Processing...</span>
              <span v-else>Pay & Upgrade</span>
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { onUnmounted, watch } from 'vue'
const props = defineProps({
  isOpen: Boolean,
  plan: Object
})
const emit = defineEmits(['close', 'success'])
const error = ref('')
const loading = ref(false)
let stripe = null
let elements = null
let cardElement = null

const closeModal = () => {
  if (cardElement) {
    cardElement.unmount()
    cardElement = null
  }
  emit('close')
}

const handleEscape = (e) => {
  if (e.key === 'Escape') {
    closeModal()
  }
}

const getStripePublishableKey = () => {
  const key = import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY || window.STRIPE_PUBLISHABLE_KEY || 'pk_test_REPLACE_WITH_YOUR_PUBLISHABLE_KEY'
  return key
}


const mountStripeElements = async () => {
  if (!window.Stripe) {
    error.value = 'Stripe.js not loaded'
    return
  }
  const container = document.getElementById('stripe-payment-element')
  if (!container) {
    error.value = 'Stripe container not found.'
    return
  }
  if (cardElement) {
    cardElement.unmount()
    cardElement = null
  }
  stripe = window.Stripe(getStripePublishableKey())
  if (typeof stripe.elements !== 'function') {
    error.value = 'Stripe initialization failed. Check your publishable key.'
    return
  }
  elements = stripe.elements()
  cardElement = elements.create('card')
  cardElement.mount('#stripe-payment-element')

}

const name = ref('')
const email = ref('')

const submitPayment = async () => {
  loading.value = true
  error.value = ''
  let clientSecret
  try {
    const response = await window.axios.post('/api/stripe/payment-intent', { plan_id: props.plan.id, name: name.value, email: email.value })
    clientSecret = response.data.client_secret
  } catch (e) {
    error.value = 'Failed to create payment intent.'
    loading.value = false
    return
  }
  const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
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
  emit('success')
  loading.value = false
}

const formatAmount = (planName) => {
  if (planName === 'base') return '$5.00';
  if (planName === 'full') return '$10.00';
  return '$0.00';
}

onMounted(() => {
  if (props.isOpen) {
    setTimeout(() => {
      mountStripeElements()
    }, 300)
  }
  if (props.isOpen) {
    window.addEventListener('keydown', handleEscape)
  }
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    window.addEventListener('keydown', handleEscape)
  } else {
    window.removeEventListener('keydown', handleEscape)
  }
})
</script>
