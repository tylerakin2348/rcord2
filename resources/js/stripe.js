// Singleton Stripe.js loader and element manager
import { ref } from 'vue'

let stripe = null
let elements = null
let cardElement = null

export function getStripeInstance() {
  if (!stripe && window.Stripe) {
    const key = import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY || window.STRIPE_PUBLISHABLE_KEY || 'pk_test_REPLACE_WITH_YOUR_PUBLISHABLE_KEY'
    stripe = window.Stripe(key)
  }
  return stripe
}

export function getStripeElements() {
  if (!elements && getStripeInstance()) {
    elements = stripe.elements()
  }
  return elements
}

export function mountCardElement(containerId = 'stripe-payment-element') {
  const container = document.getElementById(containerId)
  if (!container) return null
  // If cardElement exists and is already mounted, do not create/mount again
  if (cardElement && cardElement._mounted) return cardElement
  if (!cardElement && getStripeElements()) {
    cardElement = elements.create('card')
  }
  // Only mount if not already mounted
  if (cardElement && !cardElement._mounted) {
    cardElement.mount(`#${containerId}`)
    cardElement._mounted = true
  }
  return cardElement
}

export function unmountCardElement() {
  if (cardElement && cardElement._mounted) {
    cardElement.unmount()
    cardElement._mounted = false
  }
}

export function getCardElement() {
  return cardElement
}
