<template>
  <div class="auth-page">
    <div class="auth-card card">
      <h1>🧘 Dołącz do nas</h1>
      <p class="subtitle">Rozpocznij swoją podróż ku cyfrowemu detoksowi</p>
      
      <form @submit.prevent="handleRegister">
        <div class="form-row">
          <div class="form-group">
            <label for="firstName">Imię</label>
            <input id="firstName" v-model="form.firstName" type="text" required />
          </div>
          <div class="form-group">
            <label for="lastName">Nazwisko</label>
            <input id="lastName" v-model="form.lastName" type="text" required />
          </div>
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" v-model="form.email" type="email" required />
        </div>
        
        <div class="form-group">
          <label for="password">Hasło</label>
          <input id="password" v-model="form.password" type="password" minlength="6" required />
        </div>
        
        <p v-if="authStore.error" class="error-message">{{ authStore.error }}</p>
        <p v-if="success" class="success-message">Rejestracja udana! Możesz się teraz zalogować.</p>
        
        <button type="submit" class="btn btn-primary" :disabled="authStore.loading">
          {{ authStore.loading ? 'Rejestracja...' : 'Zarejestruj się' }}
        </button>
      </form>
      
      <p class="auth-link">
        Masz już konto? <router-link to="/login">Zaloguj się</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const success = ref(false)

const form = reactive({
  firstName: '',
  lastName: '',
  email: '',
  password: ''
})

async function handleRegister() {
  try {
    await authStore.register(form)
    success.value = true
    setTimeout(() => router.push('/login'), 2000)
  } catch (e) {
    // Error handled in store
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
  max-width: 450px;
  text-align: center;
}

.auth-card h1 { margin-bottom: 0.5rem; }
.subtitle { color: #718096; margin-bottom: 2rem; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.auth-card .btn { width: 100%; margin-top: 1rem; }
.auth-link { margin-top: 1.5rem; color: #718096; }
.auth-link a { color: var(--primary); text-decoration: none; font-weight: 500; }

.success-message {
  color: var(--success);
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>
