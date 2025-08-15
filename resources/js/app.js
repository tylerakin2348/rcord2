import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

// Create Pinia store
const pinia = createPinia();

// Create Vue app
const app = createApp(App);

// Use Pinia and Router
app.use(pinia);
app.use(router);

// Mount the app
app.mount('#app');
