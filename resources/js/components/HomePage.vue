<template>
  <LoggedInLanding v-if="store.isLoggedIn" />
  <LandingPage v-else />
</template>

<script setup>
import { useMainStore } from '../stores/main';
import LandingPage from './LandingPage.vue';
import LoggedInLanding from './LoggedInLanding.vue';

const store = useMainStore();
</script>
