
// Set Axios Authorization header from localStorage token on app load
const token = localStorage.getItem('auth_token');
if (token) {
  window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

import { defineStore } from 'pinia';

export const useMainStore = defineStore('main', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    isLoading: false,
    error: null,
    users: [],
    stats: {
      users: 1250,
      projects: 340,
      uptime: 99.9
    },
    features: [
      {
        id: 1,
        title: 'Fast & Reliable',
        description: 'Built with Vue 3 and Laravel for optimal performance',
        icon: '⚡'
      },
      {
        id: 2,
        title: 'Modern UI',
        description: 'Beautiful, responsive design with Tailwind CSS',
        icon: '🎨'
      },
      {
        id: 3,
        title: 'Real-time Updates',
        description: 'Stay connected with live data synchronization',
        icon: '🔄'
      },
      {
        id: 4,
        title: 'Secure',
        description: 'Enterprise-grade security with Laravel backend',
        icon: '🔒'
      }
    ]
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated && !!state.user,
    featuredProjects: (state) => state.features.slice(0, 3)
  },

  actions: {
    setLoading(value) {
      this.isLoading = value;
    },

    setError(error) {
      this.error = error;
    },

    clearError() {
      this.error = null;
    },

    async initialize() {
      this.isLoading = true;
      
      try {
        await this.fetchUser();
        await this.fetchStats();
        await this.fetchFeatures();
        
        if (this.isAuthenticated) {
          await this.fetchUsers();
        }
      } catch (error) {
        console.error('Failed to initialize app:', error);
      } finally {
        this.isLoading = false;
      }
    },

    async fetchUser() {
      try {
        const response = await fetch('/api/user');
        
        if (response.ok) {
          const data = await response.json();
          this.user = data.user;
          this.isAuthenticated = true;
        } else {
          this.user = null;
          this.isAuthenticated = false;
        }
      } catch (err) {
        this.user = null;
        this.isAuthenticated = false;
      }
    },

    async login(credentials) {
      this.isLoading = true;
      this.error = null;
      
      try {
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(credentials)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Login failed');
        }
        
        this.user = data.user;
        this.isAuthenticated = true;
        // Store token if present
        if (data.token) {
          localStorage.setItem('auth_token', data.token);
          window.axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;
        }
        return data;
      } catch (err) {
        this.error = err.message;
        throw err;
      } finally {
        this.isLoading = false;
      }
    },

    async register(userData) {
      this.isLoading = true;
      this.error = null;
      
      try {
        const response = await fetch('/api/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Registration failed');
        }
        
        this.user = data.user;
        this.isAuthenticated = true;
        
        return data;
      } catch (err) {
        this.error = err.message;
        throw err;
      } finally {
        this.isLoading = false;
      }
    },

    async logout() {
      this.isLoading = true;
      
      try {
        await fetch('/api/logout', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
  this.user = null;
  this.isAuthenticated = false;
  this.users = [];
  // Remove token from localStorage and Axios
  localStorage.removeItem('auth_token');
  delete window.axios.defaults.headers.common['Authorization'];
      } catch (err) {
        console.error('Logout error:', err);
      } finally {
        this.isLoading = false;
      }
    },

    // User CRUD operations
    async fetchUsers() {
      this.isLoading = true;
      
      try {
        const response = await fetch('/api/users');
        const data = await response.json();
        
        if (response.ok) {
          this.users = data.data || data;
        } else {
          throw new Error('Failed to fetch users');
        }
      } catch (err) {
        this.error = err.message;
      } finally {
        this.isLoading = false;
      }
    },

    async createUser(userData) {
      this.isLoading = true;
      this.error = null;
      
      try {
        const response = await fetch('/api/users', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Failed to create user');
        }
        
        await this.fetchUsers(); // Refresh users list
        return data;
      } catch (err) {
        this.error = err.message;
        throw err;
      } finally {
        this.isLoading = false;
      }
    },

    async updateUser(id, userData) {
      this.isLoading = true;
      this.error = null;
      
      try {
        const response = await fetch(`/api/users/${id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Failed to update user');
        }
        
        await this.fetchUsers(); // Refresh users list
        return data;
      } catch (err) {
        this.error = err.message;
        throw err;
      } finally {
        this.isLoading = false;
      }
    },

    async deleteUser(id) {
      this.isLoading = true;
      this.error = null;
      
      try {
        const response = await fetch(`/api/users/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Failed to delete user');
        }
        
        await this.fetchUsers(); // Refresh users list
        return data;
      } catch (err) {
        this.error = err.message;
        throw err;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchStats() {
      try {
        const response = await fetch('/api/stats');
        const data = await response.json();
        this.stats = data;
      } catch (err) {
        console.error('Error fetching stats:', err);
      }
    },

    async fetchFeatures() {
      try {
        const response = await fetch('/api/features');
        const data = await response.json();
        this.features = data;
      } catch (err) {
        console.error('Error fetching features:', err);
      }
    },

    updateStats(newStats) {
      this.stats = { ...this.stats, ...newStats };
    }
  }
});
