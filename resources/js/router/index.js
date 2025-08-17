import { createRouter, createWebHistory } from 'vue-router';
import { useMainStore } from '../stores/main';

// Import components
import LoggedInLanding from '../components/LoggedInLanding.vue';
import RecordingsPage from '../components/RecordingsPage.vue';
import UserProfile from '../components/UserProfile.vue';
import SystemInfo from '../components/SystemInfo.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: LoggedInLanding,
    meta: { requiresAuth: false }
  },
  {
    path: '/recordings',
    name: 'recordings',
    component: RecordingsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: UserProfile,
    meta: { requiresAuth: true }
  },
  {
    path: '/system-info',
    name: 'system-info',
    component: SystemInfo,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
  const store = useMainStore();
  
  if (to.meta.requiresAuth && !store.isLoggedIn) {
    // If route requires auth and user is not logged in, redirect to home
    next('/');
  } else {
    next();
  }
});

export default router;
