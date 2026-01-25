<template>
  <div id="app">
    <nav v-if="authStore.isAuthenticated" class="navbar">
      <div class="container">
        <router-link to="/" class="logo">🧘 Cichy Challenge</router-link>
        <div class="nav-links">
          <router-link to="/challenges">Wyzwania</router-link>
          <router-link to="/progress">Postępy</router-link>
          <router-link v-if="authStore.isAdmin" to="/admin" class="admin-link">👑 Admin</router-link>
          <button @click="logout" class="btn-logout">Wyloguj</button>
        </div>
      </div>
    </nav>
    
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  text-decoration: none;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-links a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: opacity 0.2s;
}

.nav-links a:hover {
  opacity: 0.8;
}

.btn-logout {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.3);
}

.admin-link {
  background: rgba(255, 215, 0, 0.3);
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
}

.admin-link:hover {
  background: rgba(255, 215, 0, 0.5);
}

.main-content {
  min-height: calc(100vh - 70px);
}
</style>
