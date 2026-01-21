<template>
  <div class="auth-page">
    <div class="auth-card card">
      <h1>🧘 Zaloguj się</h1>
      <p class="subtitle">Witaj ponownie w Cichy Challenge</p>
      
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input 
            id="email"
            v-model="email" 
            type="email" 
            placeholder="twoj@email.pl"
            required 
          />
        </div>
        
        <div class="form-group">
          <label for="password">Hasło</label>
          <input 
            id="password"
            v-model="password" 
            type="password" 
            placeholder="••••••••"
            required 
          />
        </div>
        
        <p v-if="authStore.error" class="error-message">{{ authStore.error }}</p>
        
        <button type="submit" class="btn btn-primary" :disabled="authStore.loading">
          {{ authStore.loading ? 'Logowanie...' : 'Zaloguj się' }}
        </button>
      </form>
      
      <p class="auth-link">
        Nie masz konta? <router-link to="/register">Zarejestruj się</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')

async function handleLogin() {
  try {
    await authStore.login(email.value, password.value)
    router.push('/challenges')
  } catch (e) {
    // Error is handled in store
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem;
}

.auth-card {
  width: 100%;
  max-width: 400px;
  text-align: center;
}

.auth-card h1 {
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #718096;
  margin-bottom: 2rem;
}

.auth-card .btn {
  width: 100%;
  margin-top: 1rem;
}

.auth-link {
  margin-top: 1.5rem;
  color: #718096;
}

.auth-link a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
}
</style>
