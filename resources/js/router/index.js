import { createRouter, createWebHistory } from 'vue-router';
import { useMainStore } from '../stores/main';

// Import components
import LandingPage from '../components/LandingPage.vue';
import RecordingsPage from '../components/RecordingsPage.vue';
import UserAccount from '../components/UserAccount.vue';
import SystemInfo from '../components/SystemInfo.vue';
import UserStorageReport from '../components/UserStorageReport.vue';
import UserActivityReport from '../components/UserActivityReport.vue';
import UserSessionsReport from '../components/UserSessionsReport.vue';
import UserManagement from '../components/UserManagement.vue';
import DemoLoggedInLanding from '../components/DemoLoggedInLanding.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: LandingPage,
    meta: { requiresAuth: false }
  },
  {
    path: '/demo',
    name: 'demo',
    component: DemoLoggedInLanding,
    meta: { requiresAuth: false }
  },
  {
    path: '/recordings',
    name: 'recordings',
    component: RecordingsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/account',
    name: 'account',
    component: UserAccount,
    meta: { requiresAuth: true }
  },
  {
    path: '/system-info',
    name: 'system-info',
    component: SystemInfo,
    meta: { requiresAuth: true }
  },
  {
    path: '/system-info/storage',
    name: 'user-storage-report',
    component: UserStorageReport,
    meta: { requiresAuth: true }
  },
  {
    path: '/system-info/activity',
    name: 'user-activity-report',
    component: UserActivityReport,
    meta: { requiresAuth: true }
  },
  {
    path: '/system-info/sessions',
    name: 'user-sessions-report',
    component: UserSessionsReport,
    meta: { requiresAuth: true }
  }
    ,
    {
      path: '/system-info/users',
      name: 'user-management',
      component: UserManagement,
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
