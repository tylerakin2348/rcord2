import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';

// Create Pinia store
const pinia = createPinia();

// Create Vue app
const app = createApp(App);

// Use Pinia
app.use(pinia);

// Mount the app
app.mount('#app');
